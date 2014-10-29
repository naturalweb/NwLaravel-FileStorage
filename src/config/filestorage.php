<?php

/*
|--------------------------------------------------------------------------
| Configuration FileStorage
|--------------------------------------------------------------------------
*/

return array(

    'default'  => 'filesystem',

    'path_tmp'  => sys_get_temp_dir(),
    
    'storages'  => array(

        'filesystem' => array(
            'root' => public_path('/uploads'),
            'host' => url('uploads'),
        ),

        's3' => array(
            'root'    => '/bucket',
            'access'  => 'your-access',
            'secret'  => 'your-secret',
        ), 

        'dropbox' => array(
            'root'   => '/folder',
            'token'  => 'your-token',
            'app'    => 'your-app',
        ),
    ),
);