import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

var IDLE = {
    yawAmp: 0.2,
    yawPeriod: 9,
    pitchAmp: 0.075,
    pitchPeriod: 6.7,
    rollAmp: 0.035,
    rollPeriod: 11.3,
    swellPeriod: 23,
    swellDepth: 0.45
};

var DRAG = {
    sensitivity: Math.PI * 2,
    maxPitch: 0.5,
    stiffness: 90,
    damping: 18,
    resumeMs: 800
};

var SCREEN = {
    emissive: 2.6,
    glassTint: 0.12
};

var FOV = 22;
var FIT_MARGIN = 1.06;

var BASE_ROTATION = { x: 0, y: Math.PI, z: 0 };

var MODEL_URL = '/assets/model/iphone.glb';
var DRACO_PATH = '/assets/lib/three/jsm/libs/draco/gltf/';

var canvas = document.getElementById('phone-canvas');
var fallback = document.getElementById('phone-fallback');
var video = document.getElementById('phone-video');

var announced = false;

function announce() {
    if (announced) {
        return;
    }

    announced = true;
    document.dispatchEvent(new CustomEvent('phone:ready'));
}

function showFallback() {
    if (canvas) {
        canvas.hidden = true;
    }

    if (fallback) {
        fallback.hidden = false;
    }

    if (video) {
        video.hidden = true;
    }

    announce();
}

if (!canvas || !App.gpu.heavy) {
    showFallback();
} else {
    setTimeout(announce, 6000);
    start();
}

function start() {
    var renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance'
    });

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1;
    renderer.debug.checkShaderErrors = false;

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(FOV, 1, 0.01, 100);

    var pmrem = new THREE.PMREMGenerator(renderer);

    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    pmrem.dispose();

    var key = new THREE.DirectionalLight(0xffffff, 1.8);

    key.position.set(2, 3, 4);
    scene.add(key);

    var rim = new THREE.DirectionalLight(0x00d2ff, 1.4);

    rim.position.set(-3, 1, -2);
    scene.add(rim);

    var videoTex = null;

    if (video) {
        videoTex = new THREE.VideoTexture(video);
        videoTex.flipY = false;
        videoTex.wrapS = THREE.RepeatWrapping;
        videoTex.repeat.x = -1;
        videoTex.offset.x = 1;
        videoTex.colorSpace = THREE.SRGBColorSpace;
        videoTex.minFilter = THREE.LinearFilter;
        videoTex.magFilter = THREE.LinearFilter;
        videoTex.generateMipmaps = false;
    }

    var draco = new DRACOLoader().setDecoderPath(DRACO_PATH);
    var loader = new GLTFLoader().setDRACOLoader(draco);

    loader.load(MODEL_URL, function (gltf) {
        draco.dispose();
        build(gltf.scene);
    }, undefined, function (error) {
        console.error('[phone] model failed to load', error);
        draco.dispose();
        renderer.dispose();
        showFallback();
    });

    function build(model) {
        model.traverse(function (object) {
            if (!object.isMesh) {
                return;
            }

            object.frustumCulled = false;

            var materials = Array.isArray(object.material) ? object.material : [object.material];

            materials.forEach(function (material) {
                if (!material) {
                    return;
                }

                if (material.name === 'OLED' && videoTex) {
                    material.map = null;
                    material.color = new THREE.Color(0x000000);
                    material.emissiveMap = videoTex;
                    material.emissive = new THREE.Color(0xffffff);
                    material.emissiveIntensity = SCREEN.emissive;
                    material.needsUpdate = true;
                }

                if (material.name === 'Glass') {
                    material.opacity = SCREEN.glassTint;
                    material.needsUpdate = true;
                }
            });
        });

        var box = new THREE.Box3().setFromObject(model);
        var size = box.getSize(new THREE.Vector3());
        var centre = box.getCenter(new THREE.Vector3());

        model.position.sub(centre);

        var pivot = new THREE.Group();

        pivot.add(model);
        pivot.rotation.set(BASE_ROTATION.x, BASE_ROTATION.y, BASE_ROTATION.z);
        scene.add(pivot);

        var swellPeak = 1 + IDLE.swellDepth;
        var roll = IDLE.rollAmp * swellPeak;
        var widestUnderYaw = Math.hypot(size.x, size.z);
        var reach = {
            x: widestUnderYaw * Math.cos(roll) + size.y * Math.sin(roll),
            y: size.y * Math.cos(roll) + widestUnderYaw * Math.sin(roll)
        };

        function fitDistance(height) {
            return height / 2 / Math.tan((FOV * Math.PI) / 360);
        }

        function resize() {
            var width = canvas.clientWidth;
            var height = canvas.clientHeight;

            if (!width || !height) {
                return;
            }

            renderer.setSize(width, height, false);
            camera.aspect = width / height;

            var byHeight = fitDistance(reach.y);
            var byWidth = fitDistance(reach.x / camera.aspect);

            camera.position.set(0, 0, Math.max(byHeight, byWidth) * FIT_MARGIN);
            camera.lookAt(0, 0, 0);
            camera.updateProjectionMatrix();
        }

        new ResizeObserver(resize).observe(canvas);
        resize();

        var user = { yaw: 0, pitch: 0 };
        var velocity = { yaw: 0, pitch: 0 };
        var dragging = false;
        var pointerId = null;
        var last = { x: 0, y: 0 };

        canvas.addEventListener('pointerdown', function (event) {
            dragging = true;
            pointerId = event.pointerId;
            last = { x: event.clientX, y: event.clientY };
            velocity.yaw = 0;
            velocity.pitch = 0;

            try {
                canvas.setPointerCapture(pointerId);
            } catch (err) {
            }

            canvas.classList.add('is-dragging');
        });

        canvas.addEventListener('pointermove', function (event) {
            if (!dragging || event.pointerId !== pointerId) {
                return;
            }

            var box = canvas.getBoundingClientRect();

            user.yaw += ((event.clientX - last.x) / box.width) * DRAG.sensitivity;
            user.pitch += ((event.clientY - last.y) / box.height) * DRAG.sensitivity * 0.5;
            user.pitch = Math.max(-DRAG.maxPitch, Math.min(DRAG.maxPitch, user.pitch));
            last = { x: event.clientX, y: event.clientY };
        });

        function endDrag(event) {
            if (!dragging || (pointerId !== null && event.pointerId !== pointerId)) {
                return;
            }

            dragging = false;

            try {
                if (pointerId !== null && canvas.hasPointerCapture(pointerId)) {
                    canvas.releasePointerCapture(pointerId);
                }
            } catch (err) {
            }

            pointerId = null;
            canvas.classList.remove('is-dragging');
        }

        canvas.addEventListener('pointerup', endDrag);
        canvas.addEventListener('pointercancel', endDrag);
        canvas.addEventListener('lostpointercapture', endDrag);

        var reduced = !App.motionOn;
        var previous = performance.now();
        var painted = false;
        var idleClock = 0;
        var idleRate = 1;

        function frame(now) {
            var dt = Math.min((now - previous) / 1000, 1 / 20);

            previous = now;

            if (!reduced) {
                var step = dt * (1000 / DRAG.resumeMs);

                idleRate = dragging ? Math.max(0, idleRate - step * 4) : Math.min(1, idleRate + step);
                idleClock += dt * idleRate;
            }

            if (!dragging) {
                ['yaw', 'pitch'].forEach(function (axis) {
                    var acceleration = -DRAG.stiffness * user[axis] - DRAG.damping * velocity[axis];

                    velocity[axis] += acceleration * dt;
                    user[axis] += velocity[axis] * dt;
                });
            }

            function wave(period) {
                return Math.sin((idleClock / period) * Math.PI * 2);
            }

            var swell = 1 + IDLE.swellDepth * wave(IDLE.swellPeriod);

            pivot.rotation.y = BASE_ROTATION.y + wave(IDLE.yawPeriod) * IDLE.yawAmp * swell + user.yaw;
            pivot.rotation.x = BASE_ROTATION.x + wave(IDLE.pitchPeriod) * IDLE.pitchAmp * swell + user.pitch;
            pivot.rotation.z = BASE_ROTATION.z + wave(IDLE.rollPeriod) * IDLE.rollAmp * swell;

            renderer.render(scene, camera);

            if (!painted) {
                painted = true;
                canvas.classList.add('is-ready');
                announce();
            }
        }

        var running = false;
        var onScreen = true;
        var waitingForGesture = false;

        function playVideo() {
            if (!video || !video.play) {
                return;
            }

            var attempt = video.play();

            if (!attempt || !attempt.catch) {
                return;
            }

            attempt.catch(function () {
                if (waitingForGesture) {
                    return;
                }

                waitingForGesture = true;

                ['pointerdown', 'keydown', 'touchstart'].forEach(function (name) {
                    window.addEventListener(name, function () {
                        waitingForGesture = false;
                        video.play().catch(function () {});
                    }, { once: true, passive: true });
                });
            });
        }

        function play() {
            if (running || !onScreen || document.hidden) {
                return;
            }

            running = true;
            previous = performance.now();
            renderer.setAnimationLoop(frame);
            playVideo();
        }

        function pause() {
            if (!running) {
                return;
            }

            running = false;
            renderer.setAnimationLoop(null);

            if (video && video.pause) {
                video.pause();
            }
        }

        new IntersectionObserver(function (entries) {
            onScreen = entries[0].isIntersecting;

            if (onScreen) {
                play();
            } else {
                pause();
            }
        }, { threshold: 0 }).observe(canvas);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                pause();
            } else {
                play();
            }
        });

        play();

        if (App.isLocal()) {
            window.phone = { pivot: pivot, camera: camera, IDLE: IDLE, DRAG: DRAG };
        }
    }
}
