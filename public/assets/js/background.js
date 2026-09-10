(function () {
    var canvas = document.getElementById('bg-canvas');

    if (!canvas || !App.gpu.heavy) {
        return;
    }

    var PARAMS = {
        color1: '#050505',
        color2: '#2563EB',
        color3: '#0C1424',
        rotation: -50,
        proportion: 1,
        scale: 0.01,
        speed: 30,
        distortion: 0,
        swirl: 50,
        swirlIterations: 16,
        softness: 47,
        offset: -299,
        shape: 0,
        shapeSize: 45
    };

    var RESOLUTION_SCALE = 0.6;
    var FPS = 30;

    var VERTEX_SHADER = [
        '#version 300 es',
        'in vec4 a_position;',
        'void main() {',
        '  gl_Position = a_position;',
        '}'
    ].join('\n');

    var FRAGMENT_SHADER = `#version 300 es
precision highp float;

uniform float u_time;
uniform float u_pixelRatio;
uniform vec2 u_resolution;

uniform float u_scale;
uniform float u_rotation;
uniform vec4 u_color1;
uniform vec4 u_color2;
uniform vec4 u_color3;
uniform float u_proportion;
uniform float u_softness;
uniform float u_shape;
uniform float u_shapeScale;
uniform float u_distortion;
uniform float u_swirl;
uniform float u_swirlIterations;

out vec4 fragColor;

#define TWO_PI 6.28318530718
#define PI 3.14159265358979323846

vec2 rotate(vec2 uv, float th) {
  return mat2(cos(th), sin(th), -sin(th), cos(th)) * uv;
}

float random(vec2 st) {
  return fract(sin(dot(st.xy, vec2(12.9898, 78.233))) * 43758.5453123);
}

float noise(vec2 st) {
  vec2 i = floor(st);
  vec2 f = fract(st);
  float a = random(i);
  float b = random(i + vec2(1.0, 0.0));
  float c = random(i + vec2(0.0, 1.0));
  float d = random(i + vec2(1.0, 1.0));

  vec2 u = f * f * (3.0 - 2.0 * f);

  float x1 = mix(a, b, u.x);
  float x2 = mix(c, d, u.x);
  return mix(x1, x2, u.y);
}

vec4 blend_colors(vec4 c1, vec4 c2, vec4 c3, float mixer, float edgesWidth, float edge_blur) {
    vec3 color1 = c1.rgb * c1.a;
    vec3 color2 = c2.rgb * c2.a;
    vec3 color3 = c3.rgb * c3.a;

    float r1 = smoothstep(.0 + .35 * edgesWidth, .7 - .35 * edgesWidth + .5 * edge_blur, mixer);
    float r2 = smoothstep(.3 + .35 * edgesWidth, 1. - .35 * edgesWidth + edge_blur, mixer);

    vec3 blended_color_2 = mix(color1, color2, r1);
    float blended_opacity_2 = mix(c1.a, c2.a, r1);

    vec3 c = mix(blended_color_2, color3, r2);
    float o = mix(blended_opacity_2, c3.a, r2);
    return vec4(c, o);
}

void main() {
    vec2 uv = gl_FragCoord.xy / u_resolution.xy;

    float t = .5 * u_time;

    float noise_scale = .0005 + .006 * u_scale;

    uv -= .5;
    uv *= (noise_scale * u_resolution);
    uv = rotate(uv, u_rotation * .5 * PI);
    uv /= u_pixelRatio;
    uv += .5;

    float n1 = noise(uv * 1. + t);
    float n2 = noise(uv * 2. - t);
    float angle = n1 * TWO_PI;
    uv.x += 4. * u_distortion * n2 * cos(angle);
    uv.y += 4. * u_distortion * n2 * sin(angle);

    float iterations_number = ceil(clamp(u_swirlIterations, 1., 30.));
    for (float i = 1.; i <= iterations_number; i++) {
        uv.x += clamp(u_swirl, 0., 2.) / i * cos(t + i * 1.5 * uv.y);
        uv.y += clamp(u_swirl, 0., 2.) / i * cos(t + i * 1. * uv.x);
    }

    float proportion = clamp(u_proportion, 0., 1.);

    float shape = 0.;
    float mixer = 0.;
    if (u_shape < .5) {
      vec2 checks_shape_uv = uv * (.5 + 3.5 * u_shapeScale);
      shape = .5 + .5 * sin(checks_shape_uv.x) * cos(checks_shape_uv.y);
      mixer = shape + .48 * sign(proportion - .5) * pow(abs(proportion - .5), .5);
    } else if (u_shape < 1.5) {
      vec2 stripes_shape_uv = uv * (.25 + 3. * u_shapeScale);
      float f = fract(stripes_shape_uv.y);
      shape = smoothstep(.0, .55, f) * smoothstep(1., .45, f);
      mixer = shape + .48 * sign(proportion - .5) * pow(abs(proportion - .5), .5);
    } else {
      float sh = 1. - uv.y;
      sh -= .5;
      sh /= (noise_scale * u_resolution.y);
      sh += .5;
      float shape_scaling = .2 * (1. - u_shapeScale);
      shape = smoothstep(.45 - shape_scaling, .55 + shape_scaling, sh + .3 * (proportion - .5));
      mixer = shape;
    }

    vec4 color_mix = blend_colors(u_color1, u_color2, u_color3, mixer, 1. - clamp(u_softness, 0., 1.), .01 + .01 * u_scale);

    fragColor = vec4(color_mix.rgb, color_mix.a);
}
`;

    function hexToRgb(hex) {
        var c = hex.replace('#', '');

        return [
            parseInt(c.slice(0, 2), 16) / 255,
            parseInt(c.slice(2, 4), 16) / 255,
            parseInt(c.slice(4, 6), 16) / 255
        ];
    }

    function compile(gl, type, source) {
        var shader = gl.createShader(type);

        gl.shaderSource(shader, source);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            console.warn('[background] shader failed to compile:', gl.getShaderInfoLog(shader));
            return null;
        }

        return shader;
    }

    var gl = canvas.getContext('webgl2', { premultipliedAlpha: true, alpha: true, antialias: true });

    if (!gl) {
        return;
    }

    var vertex = compile(gl, gl.VERTEX_SHADER, VERTEX_SHADER);
    var fragment = compile(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER);

    if (!vertex || !fragment) {
        return;
    }

    var program = gl.createProgram();

    gl.attachShader(program, vertex);
    gl.attachShader(program, fragment);
    gl.linkProgram(program);
    gl.useProgram(program);

    var buffer = gl.createBuffer();

    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]), gl.STATIC_DRAW);

    var position = gl.getAttribLocation(program, 'a_position');

    gl.enableVertexAttribArray(position);
    gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

    var uniforms = {};
    var names = ['u_time', 'u_resolution', 'u_pixelRatio', 'u_scale', 'u_rotation',
        'u_color1', 'u_color2', 'u_color3', 'u_proportion', 'u_softness',
        'u_shape', 'u_shapeScale', 'u_distortion', 'u_swirl', 'u_swirlIterations'];

    names.forEach(function (name) {
        uniforms[name] = gl.getUniformLocation(program, name);
    });

    var layer = canvas.parentElement;
    var ratio = 1;

    function resize() {
        var width = layer.clientWidth;
        var height = layer.clientHeight;

        if (!width || !height) {
            return;
        }

        ratio = (window.devicePixelRatio || 1) * RESOLUTION_SCALE;
        canvas.width = Math.max(2, Math.round(width * ratio));
        canvas.height = Math.max(2, Math.round(height * ratio));
        gl.viewport(0, 0, canvas.width, canvas.height);
    }

    resize();

    if ('ResizeObserver' in window) {
        new ResizeObserver(resize).observe(layer);
    } else {
        window.addEventListener('resize', resize);
    }

    var color1 = hexToRgb(PARAMS.color1);
    var color2 = hexToRgb(PARAMS.color2);
    var color3 = hexToRgb(PARAMS.color3);

    var startTime = performance.now();
    var lastDraw = 0;
    var minDelta = 1000 / FPS;
    var frame = 0;

    function draw(time) {
        var elapsed = (time - startTime) / 1000;
        var speed = (PARAMS.speed / 100) * 5;

        gl.uniform1f(uniforms.u_time, elapsed * speed + PARAMS.offset * 0.01);
        gl.uniform2f(uniforms.u_resolution, canvas.width, canvas.height);
        gl.uniform1f(uniforms.u_pixelRatio, ratio);
        gl.uniform1f(uniforms.u_scale, PARAMS.scale);
        gl.uniform1f(uniforms.u_rotation, (PARAMS.rotation * Math.PI) / 180);

        gl.uniform4f(uniforms.u_color1, color1[0], color1[1], color1[2], 1);
        gl.uniform4f(uniforms.u_color2, color2[0], color2[1], color2[2], 1);
        gl.uniform4f(uniforms.u_color3, color3[0], color3[1], color3[2], 1);

        gl.uniform1f(uniforms.u_proportion, PARAMS.proportion / 100);
        gl.uniform1f(uniforms.u_softness, PARAMS.softness / 100);
        gl.uniform1f(uniforms.u_shape, PARAMS.shape);
        gl.uniform1f(uniforms.u_shapeScale, PARAMS.shapeSize / 100);
        gl.uniform1f(uniforms.u_distortion, PARAMS.distortion / 50);
        gl.uniform1f(uniforms.u_swirl, PARAMS.swirl / 100);
        gl.uniform1f(uniforms.u_swirlIterations, PARAMS.swirl === 0 ? 0 : PARAMS.swirlIterations);

        gl.drawArrays(gl.TRIANGLES, 0, 6);
    }

    function loop(time) {
        frame = requestAnimationFrame(loop);

        if (time - lastDraw < minDelta) {
            return;
        }

        lastDraw = time;
        draw(time);
    }

    if (App.motionOn) {
        frame = requestAnimationFrame(loop);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                cancelAnimationFrame(frame);
            } else {
                lastDraw = 0;
                frame = requestAnimationFrame(loop);
            }
        });
    } else {
        draw(performance.now());
    }
})();
