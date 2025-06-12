<?php return array (
  'admin' => 
  array (
    'name' => 'Laravel_Admin',
    'company_name' => 'UTD Company',
    'isUsed_vip' => 'false',
    'logo-mini' => '<b>LC</b>',
    'bootstrap' => 'D:\\projects\\eagle\\app\\Admin/bootstrap.php',
    'route' => 
    array (
      'prefix' => 'admin',
      'namespace' => 'App\\Admin\\Controllers',
      'middleware' => 
      array (
        0 => 'web',
        1 => 'admin',
        2 => 'multiLanguage',
        3 => 'admin.permission:deny,agency',
        4 => 'production.error',
      ),
    ),
    'agency_route' => 
    array (
      'prefix' => 'agency',
      'namespace' => 'App\\Admin\\Controllers\\AgencyControllers',
      'middleware' => 
      array (
        0 => 'web',
        1 => 'multiLanguage',
      ),
    ),
    'directory' => 'D:\\projects\\eagle\\app\\Admin',
    'title' => 'Admin',
    'https' => false,
    'auth' => 
    array (
      'controller' => 'App\\Admin\\Controllers\\AuthController',
      'guard' => 'admin',
      'guards' => 
      array (
        'admin' => 
        array (
          'driver' => 'session',
          'provider' => 'admin',
        ),
        'preview-admin' => 
        array (
          'driver' => 'session',
          'provider' => 'preview-admin',
        ),
      ),
      'providers' => 
      array (
        'admin' => 
        array (
          'driver' => 'eloquent',
          'model' => 'App\\Models\\Admin',
          0 => 'App\\Admin\\Controllers\\AdminControllerServiceProvider',
        ),
        'preview-admin' => 
        array (
          'driver' => 'eloquent',
          'model' => 'App\\Models\\PreviewAdmin',
        ),
      ),
      'remember' => true,
      'redirect_to' => 'login',
      'excepts' => 
      array (
        0 => 'login',
        1 => 'auth/logout',
        2 => 'locale',
      ),
    ),
    'upload' => 
    array (
      'disk' => 'admin',
      'directory' => 
      array (
        'image' => 'images',
        'file' => 'files',
      ),
    ),
    'database' => 
    array (
      'connection' => '',
      'users_table' => 'admin_users',
      'users_model' => 'Encore\\Admin\\Auth\\Database\\Administrator',
      'roles_table' => 'admin_roles',
      'roles_model' => 'Encore\\Admin\\Auth\\Database\\Role',
      'permissions_table' => 'admin_permissions',
      'permissions_model' => 'Encore\\Admin\\Auth\\Database\\Permission',
      'menu_table' => 'admin_menu',
      'menu_model' => 'App\\Models\\AdminMenu',
      'operation_log_table' => 'admin_operation_log',
      'user_permissions_table' => 'admin_user_permissions',
      'role_users_table' => 'admin_role_users',
      'role_permissions_table' => 'admin_role_permissions',
      'role_menu_table' => 'admin_role_menu',
    ),
    'operation_log' => 
    array (
      'enable' => true,
      'allowed_methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
        2 => 'POST',
        3 => 'PUT',
        4 => 'DELETE',
        5 => 'CONNECT',
        6 => 'OPTIONS',
        7 => 'TRACE',
        8 => 'PATCH',
      ),
      'except' => 
      array (
        0 => 'admin/auth/logs*',
      ),
    ),
    'check_route_permission' => true,
    'check_menu_roles' => true,
    'default_avatar' => '/vendor/laravel-admin/AdminLTE/dist/img/logo_sphinx.png',
    'map_provider' => 'google',
    'skin' => 'skin-black-light',
    'layout' => 
    array (
      0 => 'sidebar-mini',
    ),
    'login_background_image' => '',
    'show_version' => true,
    'show_environment' => true,
    'menu_bind_permission' => true,
    'enable_default_breadcrumb' => true,
    'minify_assets' => 
    array (
      'excepts' => 
      array (
      ),
    ),
    'enable_menu_search' => false,
    'menu_exclude' => 
    array (
      0 => '_handle_selectable_',
      1 => '_handle_renderable_',
    ),
    'top_alert' => '',
    'grid_action_class' => 'Encore\\Admin\\Grid\\Displayers\\DropdownActions',
    'extension_dir' => 'D:\\projects\\eagle\\app\\Admin/Extensions',
    'extensions' => 
    array (
      'chartjs' => 
      array (
        'enable' => true,
      ),
      'material-ui' => 
      array (
        'enable' => false,
      ),
      'multi-language' => 
      array (
        'enable' => true,
        'default' => 'ar',
        'show-login-page' => true,
        'show-navbar' => true,
        'cookie-name' => 'locale',
        'languages' => 
        array (
          'en' => 'English',
          'ar' => 'العربية',
          'tr' => 'Türkçe',
          'hi' => 'हिन्दी',
        ),
      ),
      'cropper' => 
      array (
        'enable' => true,
      ),
      'watermark' => 
      array (
        'enable' => false,
        'config' => 
        array (
          'content' => 'yai-chat',
          'width' => '100px',
          'height' => '120px',
          'textAlign' => 'left',
          'textBaseline' => 'alphabetic',
          'font' => '15px Times New Roman',
          'fillStyle' => 'rgba(204,204,204,0.4)',
          'rotate' => 30,
          'zIndex' => 1000,
        ),
      ),
    ),
    'logo' => 'Default',
  ),
  'agency' => 
  array (
    'name' => 'Laravel_Admin',
    'logo' => '<b>Laravel</b> Chat',
    'logo-mini' => '<b>LC</b>',
    'bootstrap' => 'D:\\projects\\eagle\\app\\Admin/bootstrap.php',
    'route' => 
    array (
      'prefix' => 'agency',
      'namespace' => 'App\\Agency\\Controllers',
      'middleware' => 
      array (
        0 => 'web',
        1 => 'agency',
        2 => 'multiLanguage',
        3 => 'admin.permission:deny,agency',
      ),
    ),
    'directory' => 'D:\\projects\\eagle\\app\\Admin',
    'title' => 'Admin',
    'https' => false,
    'auth' => 
    array (
      'controller' => 'App\\Agency\\Controllers\\AuthController',
      'guard' => 'agency',
      'guards' => 
      array (
        'agency' => 
        array (
          'driver' => 'session',
          'provider' => 'agency',
        ),
      ),
      'providers' => 
      array (
        'agency' => 
        array (
          'driver' => 'eloquent',
          'model' => 'App\\Models\\Agent',
        ),
      ),
      'remember' => true,
      'redirect_to' => 'auth/login',
      'excepts' => 
      array (
        0 => 'auth/login',
        1 => 'auth/logout',
        2 => 'locale',
      ),
    ),
    'upload' => 
    array (
      'disk' => 'admin',
      'directory' => 
      array (
        'image' => 'images',
        'file' => 'files',
      ),
    ),
    'database' => 
    array (
      'connection' => '',
      'users_table' => 'admin_users',
      'users_model' => 'Encore\\Admin\\Auth\\Database\\Administrator',
      'roles_table' => 'admin_roles',
      'roles_model' => 'Encore\\Admin\\Auth\\Database\\Role',
      'permissions_table' => 'admin_permissions',
      'permissions_model' => 'Encore\\Admin\\Auth\\Database\\Permission',
      'menu_table' => 'admin_menu',
      'menu_model' => 'Encore\\Admin\\Auth\\Database\\Menu',
      'operation_log_table' => 'admin_operation_log',
      'user_permissions_table' => 'admin_user_permissions',
      'role_users_table' => 'admin_role_users',
      'role_permissions_table' => 'admin_role_permissions',
      'role_menu_table' => 'admin_role_menu',
    ),
    'operation_log' => 
    array (
      'enable' => true,
      'allowed_methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
        2 => 'POST',
        3 => 'PUT',
        4 => 'DELETE',
        5 => 'CONNECT',
        6 => 'OPTIONS',
        7 => 'TRACE',
        8 => 'PATCH',
      ),
      'except' => 
      array (
        0 => 'admin/auth/logs*',
      ),
    ),
    'check_route_permission' => true,
    'check_menu_roles' => true,
    'default_avatar' => '/vendor/laravel-admin/AdminLTE/dist/img/logo_sphinx.png',
    'map_provider' => 'google',
    'skin' => 'skin-black-light',
    'layout' => 
    array (
      0 => 'sidebar-mini',
    ),
    'login_background_image' => '',
    'show_version' => true,
    'show_environment' => true,
    'menu_bind_permission' => true,
    'enable_default_breadcrumb' => true,
    'minify_assets' => 
    array (
      'excepts' => 
      array (
      ),
    ),
    'enable_menu_search' => false,
    'menu_exclude' => 
    array (
      0 => '_handle_selectable_',
      1 => '_handle_renderable_',
    ),
    'top_alert' => '',
    'grid_action_class' => 'Encore\\Admin\\Grid\\Displayers\\Actions',
    'extension_dir' => 'D:\\projects\\eagle\\app\\Admin/Extensions',
    'extensions' => 
    array (
      'chartjs' => 
      array (
        'enable' => true,
      ),
      'material-ui' => 
      array (
        'enable' => false,
      ),
      'multi-language' => 
      array (
        'enable' => true,
        'languages' => 
        array (
          'en' => 'English',
          'ar' => 'Arabic',
        ),
        'default' => 'ar',
        'show-login-page' => true,
        'show-navbar' => true,
        'cookie-name' => 'locale',
      ),
      'cropper' => 
      array (
        'enable' => true,
      ),
      'watermark' => 
      array (
        'enable' => false,
        'config' => 
        array (
          'content' => 'yai-chat',
          'width' => '100px',
          'height' => '120px',
          'textAlign' => 'left',
          'textBaseline' => 'alphabetic',
          'font' => '15px Times New Roman',
          'fillStyle' => 'rgba(204,204,204,0.4)',
          'rotate' => 30,
          'zIndex' => 1000,
        ),
      ),
    ),
  ),
  'app' => 
  array (
    'google_cloud_file' => NULL,
    'google_cloud_storage_bucket' => NULL,
    'cache' => 'disabled',
    'agora_app_id' => NULL,
    'agora_certificate' => NULL,
    'payment_url' => NULL,
    'node_server_name' => NULL,
    'owner_timezone' => 'UTC',
    'baishun_app_id' => '4280702746',
    'baishun_app_key' => 'LzfGx3f3ZKQSYxMNRqdRTOmfd0Jb59DF',
    'baishun_server_url' => 'https://mesh-channels-test.jieyou.shop',
    'baishun_channel' => '',
    'baishun_gsp' => '',
    'balance_user_name' => 'superAdmin',
    'balance_password' => '12345678',
    'one_coins' => NULL,
    'appLogo' => 'https://demo.24hourworx.com/assets/images/BG2.jpg',
    'fileName' => 'firebase_credentials.json',
    'projectName' => '',
    'senderId' => 'hola-chat-5554d',
    'zego_credential' => '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4',
    'utd_secret_key' => '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4',
    'utd_client_id' => '3030',
    'google_client_id' => '790444932875-co6ri5d8e3m59ktv73h7eana2gfqbv34.apps.googleusercontent.com',
    'name' => 'Default',
    'name_en' => 'Laravel',
    'name_ar' => 'لارافيل',
    'env' => 'local',
    'api_prefix' => '',
    'app_origin_name' => 'r-star',
    'debug' => true,
    'url' => 'http://eagle.test',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'ar',
    'faker_locale' => 'en_US',
    'key' => 'base64:AF+rUm8xm/wsKsCXQybVHtyhc1F8L1uSy9Dw7LgZu+M=',
    'cipher' => 'AES-256-CBC',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'Barryvdh\\DomPDF\\ServiceProvider',
      23 => 'App\\Bd\\BdServiceProvider',
      24 => 'App\\Providers\\AppServiceProvider',
      25 => 'App\\Providers\\AuthServiceProvider',
      26 => 'App\\Providers\\BroadcastServiceProvider',
      27 => 'App\\Providers\\EventServiceProvider',
      28 => 'App\\Providers\\RouteServiceProvider',
      29 => 'App\\Providers\\TelescopeServiceProvider',
      30 => 'App\\Providers\\ConfigServiceProvider',
      31 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Redis' => 'Illuminate\\Support\\Facades\\Redis',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Excel' => 'Maatwebsite\\Excel\\Facades\\Excel',
      'FFMpeg' => 'ProtoneMedia\\LaravelFFMpeg\\Support\\FFMpeg',
      'RedisService' => 'App\\Facades\\RedisService',
    ),
  ),
  'apple' => 
  array (
    'apple_team_id' => 'apple team',
    'apple_key_id' => '',
    'apple_client_id' => 'asdasd',
    'apple_redirect_uri' => 'asdasdad',
    'service_file' => NULL,
    'apple_service_file' => 'asdasdad',
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'agency' => 
      array (
        'driver' => 'session',
        'provider' => 'agents',
      ),
      'whatsapp' => 
      array (
        'driver' => 'session',
        'provider' => 'whatsapp',
      ),
      'preview-admin' => 
      array (
        'driver' => 'session',
        'provider' => 'preview-admin',
      ),
      'bd' => 
      array (
        'driver' => 'session',
        'provider' => 'bd_users',
      ),
      'admin' => 
      array (
        'driver' => 'session',
        'provider' => 'admin',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\Admin',
      ),
      'admin' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\Admin',
        0 => 'App\\Admin\\Controllers\\AdminControllerServiceProvider',
      ),
      'agents' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\Agent',
      ),
      'whatsapp' => 
      array (
        'driver' => 'eloquent',
        'model' => 'Modules\\Whatsapp\\Entities\\WhatsappApp',
      ),
      'preview-admin' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\PreviewAdmin',
      ),
      'bd_users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\Bd',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_resets',
        'expire' => 60,
        'throttle' => 60,
      ),
      'whatsapp' => 
      array (
        'provider' => 'whatsapp',
        'table' => 'password_resets',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
    'controller' => 'App\\Admin\\Controllers\\AuthController',
    'guard' => 'admin',
    'remember' => true,
    'redirect_to' => 'login',
    'excepts' => 
    array (
      0 => 'login',
      1 => 'auth/logout',
      2 => 'locale',
    ),
  ),
  'bd' => 
  array (
    'route' => 
    array (
      'prefix' => 'bd',
    ),
    'https' => false,
    'secure' => false,
  ),
  'broadcasting' => 
  array (
    'default' => 'log',
    'connections' => 
    array (
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '85fcaaad6763b418baeb',
        'secret' => '3015b2f6c0c1b529e1ca',
        'app_id' => '1999232',
        'options' => 
        array (
          'cluster' => 'mt1',
          'useTLS' => true,
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
    'pusher-default' => 
    array (
      'driver' => 'pusher',
      'key' => 'e387fe2a383da1597b0c',
      'secret' => '890b3e96f64646b492e6',
      'app_id' => '1685098',
      'options' => 
      array (
        'cluster' => 'eu',
        'useTLS' => true,
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'apc' => 
      array (
        'driver' => 'apc',
      ),
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
        'lock_connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'D:\\projects\\eagle\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => 'laravel_cache',
  ),
  'chat' => 
  array (
    'name' => 'Chat',
    'relation' => 
    array (
      'conversations' => 'App\\Models\\Conversation\\Conversation',
    ),
    'user' => 
    array (
      'model' => 'App\\Models\\User',
      'table' => 'users',
    ),
    'table' => 
    array (
      'conversations_table' => 'conversations',
      'messages_table' => 'messages',
      'files_table' => 'files',
    ),
    'channel' => 
    array (
      'new_conversation_created' => 'new-conversation-created',
      'chat_room' => 'chat-room',
    ),
    'upload' => 
    array (
      'storage' => 'conversation',
    ),
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => '*',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => true,
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'voiceBoom',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'voiceBoom',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
          2 => 10,
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'voiceBoom',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'voiceBoom',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'laravel_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => 'http://eagle.test',
        'password' => NULL,
        'port' => '8000',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => 'http://eagle.test',
        'password' => NULL,
        'port' => '8000',
        'database' => '1',
      ),
    ),
  ),
  'dompdf' => 
  array (
    'show_warnings' => false,
    'public_path' => NULL,
    'convert_entities' => true,
    'options' => 
    array (
      'font_dir' => 'D:\\projects\\eagle\\storage\\fonts',
      'font_cache' => 'D:\\projects\\eagle\\storage\\fonts',
      'temp_dir' => 'C:\\Windows\\Temp',
      'chroot' => 'D:\\projects\\eagle',
      'allowed_protocols' => 
      array (
        'data://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'file://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'http://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'https://' => 
        array (
          'rules' => 
          array (
          ),
        ),
      ),
      'artifactPathValidation' => NULL,
      'log_output_file' => NULL,
      'enable_font_subsetting' => false,
      'pdf_backend' => 'CPDF',
      'default_media_type' => 'screen',
      'default_paper_size' => 'a4',
      'default_paper_orientation' => 'portrait',
      'default_font' => 'serif',
      'dpi' => 96,
      'enable_php' => false,
      'enable_javascript' => true,
      'enable_remote' => false,
      'allowed_remote_hosts' => NULL,
      'font_height_ratio' => 1.1,
      'enable_html5_parser' => true,
    ),
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => true,
        'include_separator_line' => false,
        'excel_compatibility' => false,
        'output_encoding' => '',
        'test_auto_detect' => true,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => NULL,
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'UTF-8',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
    ),
    'transactions' => 
    array (
      'handler' => 'db',
      'db' => 
      array (
        'connection' => NULL,
      ),
    ),
    'temporary_files' => 
    array (
      'local_path' => 'D:\\projects\\eagle\\storage\\framework/cache/laravel-excel',
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\projects\\eagle\\storage\\app',
      ),
      'public' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => '',
      ),
      'conversation' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/conversation',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'conversation',
      ),
      'admin' => 
      array (
        'driver' => 'gcs',
        'key_file_path' => 'D:\\projects\\eagle\\service-account.json',
        'key_file' => 
        array (
        ),
        'project_id' => 'your-project-id',
        'bucket' => 'your-bucket',
        'url' => 'https://storage.googleapis.com/',
        'path_prefix' => '',
      ),
      'profile' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/profile',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'profile',
      ),
      'custom' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\public',
        'url' => 'http://eagle.test/public',
        'visibility' => 'public',
      ),
      'ticket' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/ticket',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'ticket',
      ),
      'rooms' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/rooms',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'rooms',
      ),
      'unions' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/unions',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'unions',
      ),
      'families' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/families',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'families',
      ),
      'images' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/images',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'images',
      ),
      'videos' => 
      array (
        'driver' => 'gcs',
        'root' => 'D:\\projects\\eagle\\storage\\app/public/videos',
        'url' => 'http://eagle.test/storage',
        'visibility' => 'public',
        'path' => 'videos',
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
      ),
      'gcs' => 
      array (
        'driver' => 'gcs',
        'key_file_path' => 'D:\\projects\\eagle\\service-account.json',
        'key_file' => 
        array (
        ),
        'project_id' => 'your-project-id',
        'bucket' => 'your-bucket',
        'path_prefix' => '',
        'storage_api_uri' => NULL,
        'apiEndpoint' => NULL,
        'visibility' => 'public',
        'visibility_handler' => NULL,
        'metadata' => 
        array (
          'cacheControl' => 'public,max-age=86400',
        ),
        'url' => 'https://storage.googleapis.com/',
      ),
    ),
    'links' => 
    array (
      'D:\\projects\\eagle\\public\\storage' => 'D:\\projects\\eagle\\storage\\app/public',
    ),
  ),
  'games' => 
  array (
    'app_key' => NULL,
    'app_secret' => NULL,
    'pkg_name' => '',
  ),
  'geoip' => 
  array (
    'log_failures' => true,
    'include_currency' => true,
    'service' => NULL,
    'services' => 
    array (
      'maxmind_database' => 
      array (
        'class' => 'Torann\\GeoIP\\Services\\MaxMindDatabase',
        'database_path' => 'D:\\projects\\eagle\\storage\\app/geoip.mmdb',
        'update_url' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=&suffix=tar.gz',
        'locales' => 
        array (
          0 => 'en',
        ),
      ),
      'maxmind_api' => 
      array (
        'class' => 'Torann\\GeoIP\\Services\\MaxMindWebService',
        'user_id' => NULL,
        'license_key' => NULL,
        'locales' => 
        array (
          0 => 'en',
        ),
      ),
      'ipgeolocation' => 
      array (
        'class' => 'Torann\\GeoIP\\Services\\IPGeoLocation',
        'secure' => true,
        'key' => NULL,
        'continent_path' => 'D:\\projects\\eagle\\storage\\app/continents.json',
        'lang' => 'en',
      ),
      'ipdata' => 
      array (
        'class' => 'Torann\\GeoIP\\Services\\IPData',
        'key' => NULL,
        'secure' => true,
      ),
      'ipfinder' => 
      array (
        'class' => 'Torann\\GeoIP\\Services\\IPFinder',
        'key' => NULL,
        'secure' => true,
        'locales' => 
        array (
          0 => 'en',
        ),
      ),
    ),
    'cache' => 'none',
    'cache_tags' => 
    array (
      0 => 'torann-geoip-location',
    ),
    'cache_expires' => 30,
    'default_location' => 
    array (
      'ip' => '127.0.0.0',
      'iso_code' => 'US',
      'country' => 'United States',
      'city' => 'New Haven',
      'state' => 'CT',
      'state_name' => 'Connecticut',
      'postal_code' => '06510',
      'lat' => 41.31,
      'lon' => -72.92,
      'timezone' => 'America/New_York',
      'continent' => 'NA',
      'default' => true,
      'currency' => 'USD',
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 10,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
    ),
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper.php',
    'models_filename' => '_ide_helper_models.php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'include_factory_builders' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_model_relation_count_properties' => true,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => 'D:\\projects\\eagle/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'ignored_models' => 
    array (
    ),
    'model_hooks' => 
    array (
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
    ),
    'interfaces' => 
    array (
    ),
    'model_camel_case_properties' => false,
    'type_overrides' => 
    array (
      'integer' => 'int',
      'boolean' => 'bool',
    ),
    'include_class_docblocks' => false,
    'force_fqn' => false,
    'use_generics_annotations' => true,
    'additional_relation_types' => 
    array (
    ),
    'additional_relation_return_types' => 
    array (
    ),
    'post_migrate' => 
    array (
    ),
  ),
  'image' => 
  array (
    'driver' => 'gd',
  ),
  'laravel-ffmpeg' => 
  array (
    'ffmpeg' => 
    array (
      'binaries' => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
      'threads' => 12,
    ),
    'ffprobe' => 
    array (
      'binaries' => 'C:\\ffmpeg\\bin\\ffprobe.exe',
    ),
    'timeout' => 3600,
    'log_channel' => 'stack',
    'temporary_files_root' => 'C:\\Windows\\Temp',
    'temporary_files_encrypted_hls' => 'C:\\Windows\\Temp',
  ),
  'liap' => 
  array (
    'routing' => 
    array (
      'signed' => false,
      'middleware' => 
      array (
      ),
      'prefix' => '',
    ),
    'google_play_package_name' => 'com.some.thing',
    'appstore_password' => '',
    'eventListeners' => 
    array (
    ),
    'appstore_private_key_id' => NULL,
    'appstore_private_key' => NULL,
    'appstore_issuer_id' => NULL,
    'appstore_bundle_id' => NULL,
  ),
  'location' => 
  array (
    'driver' => 'Stevebauman\\Location\\Drivers\\IpApi',
    'fallbacks' => 
    array (
      0 => 'Stevebauman\\Location\\Drivers\\Ip2locationio',
      1 => 'Stevebauman\\Location\\Drivers\\IpInfo',
      2 => 'Stevebauman\\Location\\Drivers\\GeoPlugin',
      3 => 'Stevebauman\\Location\\Drivers\\MaxMind',
    ),
    'position' => 'Stevebauman\\Location\\Position',
    'http' => 
    array (
      'timeout' => 3,
      'connect_timeout' => 3,
    ),
    'testing' => 
    array (
      'ip' => '66.102.0.0',
      'enabled' => true,
    ),
    'maxmind' => 
    array (
      'license_key' => NULL,
      'web' => 
      array (
        'enabled' => false,
        'user_id' => NULL,
        'options' => 
        array (
          'host' => 'geoip.maxmind.com',
        ),
      ),
      'local' => 
      array (
        'type' => 'city',
        'path' => 'D:\\projects\\eagle\\database\\maxmind/GeoLite2-City.mmdb',
        'url' => 'https://download.maxmind.com/app/geoip_download_by_token?edition_id=GeoLite2-City&license_key=&suffix=tar.gz',
      ),
    ),
    'ip_api' => 
    array (
      'token' => NULL,
    ),
    'ipinfo' => 
    array (
      'token' => NULL,
    ),
    'ipdata' => 
    array (
      'token' => NULL,
    ),
    'ip2locationio' => 
    array (
      'token' => NULL,
    ),
    'kloudend' => 
    array (
      'token' => NULL,
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => NULL,
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'D:\\projects\\eagle\\storage\\logs/laravel.log',
        'level' => 'debug',
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'D:\\projects\\eagle\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
      ),
      'lucky_gift' => 
      array (
        'driver' => 'single',
        'path' => 'D:\\projects\\eagle\\storage\\logs/lucky_gift.log',
        'level' => 'debug',
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'D:\\projects\\eagle\\storage\\logs/laravel.log',
      ),
      'custom_log' => 
      array (
        'driver' => 'single',
        'path' => 'D:\\projects\\eagle\\storage\\logs/custom.log',
        'level' => 'debug',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => '587',
        'encryption' => 'tls',
        'username' => '',
        'password' => '',
        'timeout' => NULL,
        'auth_mode' => NULL,
        'sendmail' => '/usr/sbin/sendmail -bs',
        'pretend' => false,
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'mailgun' => 
      array (
        'transport' => 'mailgun',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -t -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
      ),
      'stream' => 
      array (
        'ssl' => 
        array (
          'allow_self_signed' => true,
          'verify_peer' => false,
          'verify_peer_name' => false,
        ),
      ),
    ),
    'from' => 
    array (
      'address' => '',
      'name' => '',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'D:\\projects\\eagle\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'modules' => 
  array (
    'namespace' => 'Modules',
    'stubs' => 
    array (
      'enabled' => false,
      'path' => 'D:\\projects\\eagle/vendor/nwidart/laravel-modules/src/Commands/stubs',
      'files' => 
      array (
        'routes/web' => 'Routes/web.php',
        'routes/api' => 'Routes/api.php',
        'views/index' => 'Resources/views/index.blade.php',
        'views/master' => 'Resources/views/layouts/master.blade.php',
        'scaffold/config' => 'Config/config.php',
        'composer' => 'composer.json',
        'assets/js/app' => 'Resources/assets/js/app.js',
        'assets/sass/app' => 'Resources/assets/sass/app.scss',
        'webpack' => 'webpack.mix.js',
        'package' => 'package.json',
      ),
      'replacements' => 
      array (
        'routes/web' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'routes/api' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'webpack' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'json' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'MODULE_NAMESPACE',
          3 => 'PROVIDER_NAMESPACE',
        ),
        'views/index' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'views/master' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'scaffold/config' => 
        array (
          0 => 'STUDLY_NAME',
        ),
        'composer' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'VENDOR',
          3 => 'AUTHOR_NAME',
          4 => 'AUTHOR_EMAIL',
          5 => 'MODULE_NAMESPACE',
          6 => 'PROVIDER_NAMESPACE',
        ),
      ),
      'gitkeep' => true,
    ),
    'paths' => 
    array (
      'modules' => 'D:\\projects\\eagle\\Modules',
      'assets' => 'D:\\projects\\eagle\\public\\modules',
      'migration' => 'D:\\projects\\eagle\\database/migrations',
      'generator' => 
      array (
        'config' => 
        array (
          'path' => 'Config',
          'generate' => true,
        ),
        'command' => 
        array (
          'path' => 'Console',
          'generate' => true,
        ),
        'migration' => 
        array (
          'path' => 'Database/Migrations',
          'generate' => true,
        ),
        'seeder' => 
        array (
          'path' => 'Database/Seeders',
          'generate' => true,
        ),
        'factory' => 
        array (
          'path' => 'Database/factories',
          'generate' => true,
        ),
        'model' => 
        array (
          'path' => 'Entities',
          'generate' => true,
        ),
        'routes' => 
        array (
          'path' => 'Routes',
          'generate' => true,
        ),
        'controller' => 
        array (
          'path' => 'Http/Controllers',
          'generate' => true,
        ),
        'filter' => 
        array (
          'path' => 'Http/Middleware',
          'generate' => true,
        ),
        'request' => 
        array (
          'path' => 'Http/Requests',
          'generate' => true,
        ),
        'provider' => 
        array (
          'path' => 'Providers',
          'generate' => true,
        ),
        'assets' => 
        array (
          'path' => 'Resources/assets',
          'generate' => true,
        ),
        'lang' => 
        array (
          'path' => 'Resources/lang',
          'generate' => true,
        ),
        'views' => 
        array (
          'path' => 'Resources/views',
          'generate' => true,
        ),
        'test' => 
        array (
          'path' => 'Tests/Unit',
          'generate' => true,
        ),
        'test-feature' => 
        array (
          'path' => 'Tests/Feature',
          'generate' => true,
        ),
        'repository' => 
        array (
          'path' => 'Repositories',
          'generate' => false,
        ),
        'event' => 
        array (
          'path' => 'Events',
          'generate' => false,
        ),
        'listener' => 
        array (
          'path' => 'Listeners',
          'generate' => false,
        ),
        'policies' => 
        array (
          'path' => 'Policies',
          'generate' => false,
        ),
        'rules' => 
        array (
          'path' => 'Rules',
          'generate' => false,
        ),
        'jobs' => 
        array (
          'path' => 'Jobs',
          'generate' => false,
        ),
        'emails' => 
        array (
          'path' => 'Emails',
          'generate' => false,
        ),
        'notifications' => 
        array (
          'path' => 'Notifications',
          'generate' => false,
        ),
        'resource' => 
        array (
          'path' => 'Transformers',
          'generate' => false,
        ),
      ),
    ),
    'scan' => 
    array (
      'enabled' => false,
      'paths' => 
      array (
        0 => 'D:\\projects\\eagle\\vendor/*/*',
      ),
    ),
    'composer' => 
    array (
      'vendor' => 'nwidart',
      'author' => 
      array (
        'name' => 'Nicolas Widart',
        'email' => 'n.widart@gmail.com',
      ),
    ),
    'cache' => 
    array (
      'enabled' => false,
      'key' => 'laravel-modules',
      'lifetime' => 60,
    ),
    'register' => 
    array (
      'translations' => true,
      'files' => 'register',
    ),
    'activators' => 
    array (
      'file' => 
      array (
        'class' => 'Nwidart\\Modules\\Activators\\FileActivator',
        'statuses-file' => 'D:\\projects\\eagle\\modules_statuses.json',
        'cache-key' => 'activator.installed',
        'cache-lifetime' => 604800,
      ),
    ),
    'activator' => 'file',
  ),
  'nafezly-payments' => 
  array (
    'PAYMOB_API_KEY' => NULL,
    'PAYMOB_INTEGRATION_ID' => NULL,
    'PAYMOB_IFRAME_ID' => NULL,
    'PAYMOB_HMAC' => NULL,
    'PAYMOB_CURRENCY' => 'EGP',
    'HYPERPAY_BASE_URL' => 'https://eu-test.oppwa.com',
    'HYPERPAY_URL' => '/v1/checkouts',
    'HYPERPAY_TOKEN' => NULL,
    'HYPERPAY_CREDIT_ID' => NULL,
    'HYPERPAY_MADA_ID' => NULL,
    'HYPERPAY_APPLE_ID' => NULL,
    'HYPERPAY_CURRENCY' => 'SAR',
    'KASHIER_ACCOUNT_KEY' => NULL,
    'KASHIER_IFRAME_KEY' => NULL,
    'KASHIER_TOKEN' => NULL,
    'KASHIER_URL' => 'https://checkout.kashier.io',
    'KASHIER_MODE' => 'test',
    'KASHIER_CURRENCY' => 'EGP',
    'KASHIER_WEBHOOK_URL' => NULL,
    'FAWRY_URL' => 'https://atfawry.fawrystaging.com/',
    'FAWRY_SECRET' => NULL,
    'FAWRY_MERCHANT' => NULL,
    'FAWRY_DISPLAY_MODE' => 'POPUP',
    'FAWRY_PAY_MODE' => 'CARD',
    'PAYPAL_CLIENT_ID' => NULL,
    'PAYPAL_SECRET' => NULL,
    'PAYPAL_CURRENCY' => 'USD',
    'PAYPAL_MODE' => 'sandbox',
    'THAWANI_API_KEY' => '',
    'THAWANI_URL' => 'https://uatcheckout.thawani.om/',
    'THAWANI_PUBLISHABLE_KEY' => '',
    'TAP_CURRENCY' => 'USD',
    'TAP_SECRET_KEY' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
    'TAP_PUBLIC_KEY' => 'pk_test_EtHFV4BuPQokJT6jiROls87Y',
    'TAP_LANG_KEY' => 'ar',
    'OPAY_CURRENCY' => 'usd',
    'OPAY_SECRET_KEY' => 'Sit dignissimos aliq',
    'OPAY_PUBLIC_KEY' => '1',
    'OPAY_MERCHANT_ID' => 'usd',
    'OPAY_COUNTRY_CODE' => 'webhook',
    'OPAY_BASE_URL' => 'webhook',
    'PAYMOB_WALLET_INTEGRATION_ID' => NULL,
    'PAYTABS_PROFILE_ID' => NULL,
    'PAYTABS_SERVER_KEY' => NULL,
    'PAYTABS_BASE_URL' => 'https://secure-egypt.paytabs.com',
    'PAYTABS_CHECKOUT_LANG' => 'AR',
    'PAYTABS_CURRENCY' => 'EGP',
    'BINANCE_API' => NULL,
    'BINANCE_SECRET' => NULL,
    'NOWPAYMENTS_API_KEY' => NULL,
    'PAYEER_MERCHANT_ID' => NULL,
    'PAYEER_API_KEY' => NULL,
    'PAYEER_ADDITIONAL_API_KEY' => NULL,
    'PERFECT_MONEY_ID' => 'UXXXXXXX',
    'PERFECT_MONEY_PASSPHRASE' => NULL,
    'VERIFY_ROUTE_NAME' => '/api/payment-callback',
    'APP_NAME' => 'Laravel',
    'TELR_MERCHANT_ID' => NULL,
    'TELR_API_KEY' => NULL,
    'TELR_MODE' => 'test',
    'CLICKPAY_SERVER_KEY' => NULL,
    'CLICKPAY_PROFILE_ID' => NULL,
    'OPAY_WEBHOOK_URL' => 'webhook',
  ),
  'paypal' => 
  array (
    'base_url' => 'https://api-m.sandbox.paypal.com',
    'mode' => 'sandbox',
    'client_id' => 'AbMnfYHyLXWcRTct1RGWW5tPFnd6SryR0ALvRMgSG4PQW5oV8uti7fYOTmQvXLPzGVJSOgZTrmJ_NpKR',
    'client_secret' => 'EHWD7-rBHXm74Dv8e9_-EMYRWMt1LAt7qzmW-YW3pOBWdLPCVs-D5hoAwfqYyS7cNW5A7Uv3IyqUgqyA',
    'currency' => 'USD',
  ),
  'paysky' => 
  array (
    'api_key' => 'https://www.google.com',
    'merchant_id' => 'Sit dignissimos aliq',
    'terminal_id' => '1',
    'base_url' => 'Impedit laborum bla',
  ),
  'pdf' => 
  array (
    'mode' => 'utf-8',
    'format' => 'A4',
    'author' => '',
    'subject' => '',
    'keywords' => '',
    'creator' => 'Laravel Pdf',
    'display_mode' => 'fullpage',
    'tempDir' => 'D:\\projects\\eagle\\storage/app/mpdf',
    'pdf_a' => false,
    'pdf_a_auto' => false,
    'icc_profile_path' => '',
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'luckyBox' => 
      array (
        'driver' => 'database',
        'connection' => 'default',
        'queue' => 'luckyBox',
        'retry_after' => 90,
        'block_for' => NULL,
      ),
      'heavyProcessing' => 
      array (
        'driver' => 'sync',
        'connection' => 'default',
        'queue' => 
        array (
          0 => 'heavy1',
          1 => 'heavy2',
          2 => 'heavy3',
        ),
        'retry_after' => 90,
        'block_for' => NULL,
      ),
      'sendComment' => 
      array (
        'driver' => 'database',
        'connection' => 'default',
        'queue' => 'sendComment',
      ),
      'notification' => 
      array (
        'driver' => 'database',
        'connection' => 'default',
        'queue' => 
        array (
          0 => 'default',
          1 => 'heavy1',
        ),
        'retry_after' => 90,
        'block_for' => NULL,
      ),
      'luckyGift' => 
      array (
        'driver' => 'database',
        'connection' => 'default',
        'queue' => 
        array (
          0 => 'lucky_gift',
          1 => 'lucky_gift_2',
          2 => 'lucky_gift_3',
        ),
        'retry_after' => 90,
        'block_for' => NULL,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => 'eagle.test',
    ),
    'guard' => 
    array (
      0 => 'web',
      1 => 'whatsapp',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'verify_csrf_token' => 'App\\Http\\Middleware\\VerifyCsrfToken',
      'encrypt_cookies' => 'App\\Http\\Middleware\\EncryptCookies',
    ),
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
      'endpoint' => 'api.mailgun.net',
    ),
    'now_payments' => 
    array (
      'api_key' => NULL,
      'callback_url' => NULL,
    ),
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'baishun' => 
    array (
      'app_id' => '4280702746',
      'app_key' => 'LzfGx3f3ZKQSYxMNRqdRTOmfd0Jb59DF',
      'server_url' => 'https://mesh-channels-test.jieyou.shop',
    ),
    'fawry' => 
    array (
      'fawry_secret' => 'fiest1',
      'fawry_merchant_code' => 'Impedit laborum bla',
      'utd_url' => 'Sit dignissimos aliq',
      'fawry_return_url' => 'https://www.google.com',
      'fawry_url' => 'https://www.google.com',
      'fawry_webhook_url' => 'http://eagle.test/api/fawry-callback',
    ),
    'zinipay' => 
    array (
      'api_key' => '',
      'url' => '',
    ),
    'agora' => 
    array (
      'app_id' => NULL,
      'app_certificate' => NULL,
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'D:\\projects\\eagle\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
  ),
  'stripe' => 
  array (
    'test_secret_key' => 'Impedit laborum bla',
    'success_url' => 'Sit dignissimos aliq',
    'cancel_url' => '1',
    'currency' => 'usd',
    'webhook_secret' => 'webhook',
    'webhook_url' => 'http://eagle.test/api/stripe-callback',
  ),
  'telescope' => 
  array (
    'enabled' => true,
    'domain' => NULL,
    'path' => 'telescope',
    'driver' => 'database',
    'storage' => 
    array (
      'database' => 
      array (
        'connection' => 'mysql',
        'chunk' => 1000,
      ),
    ),
    'queue' => 
    array (
      'connection' => NULL,
      'queue' => NULL,
    ),
    'middleware' => 
    array (
      0 => 'web',
      1 => 'Laravel\\Telescope\\Http\\Middleware\\Authorize',
    ),
    'only_paths' => 
    array (
    ),
    'ignore_paths' => 
    array (
      0 => 'livewire*',
      1 => 'nova-api*',
      2 => 'pulse*',
    ),
    'ignore_commands' => 
    array (
    ),
    'watchers' => 
    array (
      'Laravel\\Telescope\\Watchers\\BatchWatcher' => true,
      'Laravel\\Telescope\\Watchers\\CacheWatcher' => 
      array (
        'enabled' => true,
        'hidden' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ClientRequestWatcher' => true,
      'Laravel\\Telescope\\Watchers\\CommandWatcher' => 
      array (
        'enabled' => true,
        'ignore' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\DumpWatcher' => 
      array (
        'enabled' => true,
        'always' => false,
      ),
      'Laravel\\Telescope\\Watchers\\EventWatcher' => 
      array (
        'enabled' => true,
        'ignore' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ExceptionWatcher' => true,
      'Laravel\\Telescope\\Watchers\\GateWatcher' => 
      array (
        'enabled' => true,
        'ignore_abilities' => 
        array (
        ),
        'ignore_packages' => true,
        'ignore_paths' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\JobWatcher' => true,
      'Laravel\\Telescope\\Watchers\\LogWatcher' => 
      array (
        'enabled' => true,
        'level' => 'error',
      ),
      'Laravel\\Telescope\\Watchers\\MailWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ModelWatcher' => 
      array (
        'enabled' => true,
        'events' => 
        array (
          0 => 'eloquent.*',
        ),
        'hydrations' => true,
      ),
      'Laravel\\Telescope\\Watchers\\NotificationWatcher' => true,
      'Laravel\\Telescope\\Watchers\\QueryWatcher' => 
      array (
        'enabled' => true,
        'ignore_packages' => true,
        'ignore_paths' => 
        array (
        ),
        'slow' => 100,
      ),
      'Laravel\\Telescope\\Watchers\\RedisWatcher' => true,
      'Laravel\\Telescope\\Watchers\\RequestWatcher' => 
      array (
        'enabled' => true,
        'size_limit' => 64,
        'ignore_http_methods' => 
        array (
        ),
        'ignore_status_codes' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ScheduleWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ViewWatcher' => true,
    ),
  ),
  'themes' => 
  array (
    'primaryColor' => '#00ffcc',
    'secondaryColor' => '#ffffff',
    'textPrimaryColor' => '#fdf8f8',
    'textSecondaryColor' => '#000000',
    'boxBackgroundColor' => '#969696',
    'backgroundImage' => '',
    'brandBackgroundImage' => '',
    'tableBackGroundColor' => '#c88213',
  ),
  'twilio' => 
  array (
    'sid' => '',
    'api_key' => '',
    'from' => '',
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'D:\\projects\\eagle\\resources\\views',
    ),
    'compiled' => 'D:\\projects\\eagle\\storage\\framework\\views',
    'whatsapp_url' => NULL,
    'whatsapp_token' => NULL,
    'decrypt_key' => 'L9:65W&+nG@g',
    'merchant_name' => 'software',
    'apple_endpoint' => NULL,
    'merchant_id' => 'BCR2DN4T3GPIDDAG',
  ),
  'websockets' => 
  array (
    'dashboard' => 
    array (
      'port' => 6003,
    ),
    'apps' => 
    array (
      0 => 
      array (
        'id' => '1685098',
        'name' => 'Laravel',
        'key' => 'e387fe2a383da1597b0c',
        'secret' => '890b3e96f64646b492e6',
        'path' => NULL,
        'capacity' => NULL,
        'enable_client_messages' => true,
        'enable_statistics' => true,
      ),
    ),
    'app_provider' => 'BeyondCode\\LaravelWebSockets\\Apps\\ConfigAppProvider',
    'allowed_origins' => 
    array (
    ),
    'max_request_size_in_kb' => 250,
    'path' => 'laravel-websockets',
    'middleware' => 
    array (
      0 => 'web',
      1 => 'BeyondCode\\LaravelWebSockets\\Dashboard\\Http\\Middleware\\Authorize',
      2 => 'api',
    ),
    'statistics' => 
    array (
      'model' => 'BeyondCode\\LaravelWebSockets\\Statistics\\Models\\WebSocketsStatisticsEntry',
      'logger' => 'BeyondCode\\LaravelWebSockets\\Statistics\\Logger\\HttpStatisticsLogger',
      'interval_in_seconds' => 60,
      'delete_statistics_older_than_days' => 60,
      'perform_dns_lookup' => false,
    ),
    'ssl' => 
    array (
      'local_cert' => NULL,
      'local_pk' => NULL,
      'passphrase' => NULL,
    ),
    'channel_manager' => 'BeyondCode\\LaravelWebSockets\\WebSockets\\Channels\\ChannelManagers\\ArrayChannelManager',
  ),
  'firebase' => 
  array (
    'default' => 'app',
    'projects' => 
    array (
      'app' => 
      array (
        'credentials' => NULL,
        'auth' => 
        array (
          'tenant_id' => NULL,
        ),
        'firestore' => 
        array (
        ),
        'database' => 
        array (
          'url' => NULL,
        ),
        'dynamic_links' => 
        array (
          'default_domain' => NULL,
        ),
        'storage' => 
        array (
          'default_bucket' => NULL,
        ),
        'cache_store' => 'file',
        'logging' => 
        array (
          'http_log_channel' => NULL,
          'http_debug_log_channel' => NULL,
        ),
        'http_client_options' => 
        array (
          'proxy' => NULL,
          'timeout' => NULL,
          'guzzle_middlewares' => 
          array (
          ),
        ),
      ),
    ),
  ),
  'flare' => 
  array (
    'key' => NULL,
    'flare_middleware' => 
    array (
      0 => 'Spatie\\FlareClient\\FlareMiddleware\\RemoveRequestIp',
      1 => 'Spatie\\FlareClient\\FlareMiddleware\\AddGitInformation',
      2 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddNotifierName',
      3 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddEnvironmentInformation',
      4 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionInformation',
      5 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddDumps',
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddLogs' => 
      array (
        'maximum_number_of_collected_logs' => 200,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddQueries' => 
      array (
        'maximum_number_of_collected_queries' => 200,
        'report_query_bindings' => true,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddJobs' => 
      array (
        'max_chained_job_reporting_depth' => 5,
      ),
      6 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddContext',
      7 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionHandledStatus',
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestBodyFields' => 
      array (
        'censor_fields' => 
        array (
          0 => 'password',
          1 => 'password_confirmation',
        ),
      ),
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestHeaders' => 
      array (
        'headers' => 
        array (
          0 => 'API-KEY',
          1 => 'Authorization',
          2 => 'Cookie',
          3 => 'Set-Cookie',
          4 => 'X-CSRF-TOKEN',
          5 => 'X-XSRF-TOKEN',
        ),
      ),
    ),
    'send_logs_as_events' => true,
  ),
  'ignition' => 
  array (
    'editor' => 'phpstorm',
    'theme' => 'auto',
    'enable_share_button' => true,
    'register_commands' => false,
    'solution_providers' => 
    array (
      0 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\BadMethodCallSolutionProvider',
      1 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\MergeConflictSolutionProvider',
      2 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\UndefinedPropertySolutionProvider',
      3 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\IncorrectValetDbCredentialsSolutionProvider',
      4 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingAppKeySolutionProvider',
      5 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\DefaultDbNameSolutionProvider',
      6 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\TableNotFoundSolutionProvider',
      7 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingImportSolutionProvider',
      8 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\InvalidRouteActionSolutionProvider',
      9 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\ViewNotFoundSolutionProvider',
      10 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\RunningLaravelDuskInProductionProvider',
      11 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingColumnSolutionProvider',
      12 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownValidationSolutionProvider',
      13 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingMixManifestSolutionProvider',
      14 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingViteManifestSolutionProvider',
      15 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingLivewireComponentSolutionProvider',
      16 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UndefinedViewVariableSolutionProvider',
      17 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\GenericLaravelExceptionSolutionProvider',
      18 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\OpenAiSolutionProvider',
      19 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\SailNetworkSolutionProvider',
      20 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMysql8CollationSolutionProvider',
      21 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMariadbCollationSolutionProvider',
    ),
    'ignored_solution_providers' => 
    array (
    ),
    'enable_runnable_solutions' => NULL,
    'remote_sites_path' => 'D:\\projects\\eagle',
    'local_sites_path' => '',
    'housekeeping_endpoint_prefix' => '_ignition',
    'settings_file_path' => '',
    'recorders' => 
    array (
      0 => 'Spatie\\LaravelIgnition\\Recorders\\DumpRecorder\\DumpRecorder',
      1 => 'Spatie\\LaravelIgnition\\Recorders\\JobRecorder\\JobRecorder',
      2 => 'Spatie\\LaravelIgnition\\Recorders\\LogRecorder\\LogRecorder',
      3 => 'Spatie\\LaravelIgnition\\Recorders\\QueryRecorder\\QueryRecorder',
    ),
    'open_ai_key' => NULL,
    'with_stack_frame_arguments' => true,
    'argument_reducers' => 
    array (
      0 => 'Spatie\\Backtrace\\Arguments\\Reducers\\BaseTypeArgumentReducer',
      1 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ArrayArgumentReducer',
      2 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StdClassArgumentReducer',
      3 => 'Spatie\\Backtrace\\Arguments\\Reducers\\EnumArgumentReducer',
      4 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ClosureArgumentReducer',
      5 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeArgumentReducer',
      6 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeZoneArgumentReducer',
      7 => 'Spatie\\Backtrace\\Arguments\\Reducers\\SymphonyRequestArgumentReducer',
      8 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\ModelArgumentReducer',
      9 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\CollectionArgumentReducer',
      10 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StringableArgumentReducer',
    ),
  ),
  'is_fawry_active' => '0',
  'is_paysky_active' => 0,
  'is_stripe_active' => 0,
  'is_opay_active' => 0,
  'is_applepay_active' => 0,
  'exp_percentages' => 
  array (
    'exp_sender_percentage' => '100',
    'exp_received_percentage' => '3',
    'exp_cp_percentage' => 1,
    'exp_room_percentage' => 1,
    'exp_charge_percentage' => 1,
  ),
  'achievement' => 
  array (
    'name' => 'Achievement',
  ),
  'agencyapp' => 
  array (
    'name' => 'AgencyApp',
  ),
  'cp' => 
  array (
    'name' => 'CP',
  ),
  'charizma' => 
  array (
    'name' => 'Charizma',
  ),
  'dailyprize' => 
  array (
    'name' => 'DailyPrize',
    'count_key' => 'daily-gift-count',
  ),
  'events' => 
  array (
    'name' => 'Events',
  ),
  'fixedtarget' => 
  array (
    'name' => 'FixedTarget',
  ),
  'moment' => 
  array (
    'name' => 'Moment',
  ),
  'payment' => 
  array (
    'name' => 'Payment',
    'cashfree' => 
    array (
      'app_id' => NULL,
      'secret_key' => NULL,
      'mode' => NULL,
    ),
  ),
  'public' => 
  array (
    'name' => 'Public',
  ),
  'reals' => 
  array (
    'name' => 'Reals',
  ),
  'salaryTransaction' => 
  array (
    'name' => 'SalaryTransaction',
  ),
  'servercontrol' => 
  array (
    'name' => 'ServerControl',
  ),
  'specialid' => 
  array (
    'name' => 'SpecialId',
  ),
  'switchaccount' => 
  array (
    'name' => 'SwitchAccount',
  ),
  'tasks' => 
  array (
    'name' => 'Tasks',
  ),
  'wallet' => 
  array (
    'name' => 'Wallet',
  ),
  'whatsappauth' => 
  array (
    'name' => 'WhatsappAuth',
    'server_url' => 'https://tik-chat.com/api/verification_code_service',
    'server_url_login' => 'https://tik-chat.com/api/server/auth/login',
    'username' => NULL,
    'password' => NULL,
    'whatsapp_token' => NULL,
    'base_url' => NULL,
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
