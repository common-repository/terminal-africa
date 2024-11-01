<?php return array(
    'root' => array(
        'name' => 'terminalafrica/terminal-africa',
        'pretty_version' => '1.11.6',
        'version' => '1.11.6.0',
        'reference' => null,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'johnpbloch/wordpress-core-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'roots/wordpress' => array(
            'pretty_version' => '6.5.5',
            'version' => '6.5.5.0',
            'reference' => '41ff6e23ccbc3a1691406d69fe8c211a225514e2',
            'type' => 'metapackage',
            'install_path' => null,
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roots/wordpress-core-installer' => array(
            'pretty_version' => '1.100.0',
            'version' => '1.100.0.0',
            'reference' => '73f8488e5178c5d54234b919f823a9095e2b1847',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/../roots/wordpress-core-installer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roots/wordpress-no-content' => array(
            'pretty_version' => '6.5.5',
            'version' => '6.5.5.0',
            'reference' => '6.5.5',
            'type' => 'wordpress-core',
            'install_path' => __DIR__ . '/../../wordpress',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'terminalafrica/terminal-africa' => array(
            'pretty_version' => '1.11.6',
            'version' => '1.11.6.0',
            'reference' => null,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wordpress/core-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '6.5.5',
            ),
        ),
    ),
);
