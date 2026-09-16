<?php

return array(
    '/' => array('HomeController', 'index'),
    '/contact' => array('ContactController', 'send'),
    '/admin' => array('AdminController', 'index'),
    '/admin/login' => array('AdminController', 'login'),
    '/admin/logout' => array('AdminController', 'logout'),
    '/admin/status' => array('AdminController', 'status'),
    '/admin/get' => array('AdminController', 'get'),
    '/admin/save' => array('AdminController', 'save'),
    '/admin/upload' => array('AdminController', 'upload'),
    '/admin/credentials-update' => array('AdminController', 'credentialsUpdate'),
    '/admin/profile' => array('AdminController', 'profile'),
    '/admin/experience' => array('AdminController', 'experience'),
    '/admin/skills' => array('AdminController', 'skills'),
    '/admin/credentials' => array('AdminController', 'credentials'),
    '/admin/sections' => array('AdminController', 'sections'),
    '/admin/contact' => array('AdminController', 'contact'),
);
