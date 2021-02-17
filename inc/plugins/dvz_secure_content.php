<?php
/* by Tomasz 'Devilshakerz' Mlynski [devilshakerz.com]; Copyright (C) 2015-2020
 released under Creative Commons BY-NC-SA 4.0 license: https://creativecommons.org/licenses/by-nc-sa/4.0/ */

$plugins->add_hook('parse_message', ['dvz_sc', 'parse_message']);
$plugins->add_hook('usercp_avatar_start', ['dvz_sc', 'usercp_avatar_start']);
$plugins->add_hook('usercp_avatar_intermediate', ['dvz_sc', 'usercp_avatar_intermediate']);
$plugins->add_hook('usercp_do_avatar_start', ['dvz_sc', 'usercp_do_avatar_start']);
$plugins->add_hook('usercp_do_avatar_end', ['dvz_sc', 'usercp_do_avatar_end']);
$plugins->add_hook('modcp_do_editprofile_end', ['dvz_sc', 'modcp_do_editprofile_end']);
$plugins->add_hook('admin_user_users_edit_graph', ['dvz_sc', 'admin_user_users_edit_graph']);
$plugins->add_hook('admin_user_users_edit', ['dvz_sc', 'admin_user_users_edit']);
$plugins->add_hook('admin_user_users_edit_commit_start', ['dvz_sc', 'admin_user_users_edit_commit_start']);
$plugins->add_hook('admin_config_settings_begin', ['dvz_sc', 'admin_config_settings_begin']);
$plugins->add_hook('admin_settings_print_peekers', ['dvz_sc', 'admin_settings_print_peekers']);
$plugins->add_hook('admin_config_plugins_begin', ['dvz_sc', 'admin_config_plugins_begin']);

function dvz_secure_content_info()
{
    return [
        'name'           => 'DVZ Secure Content',
        'description'    => 'Filters and forwards user-generated content from insecure protocols (non-HTTPS).' . dvz_sc::description_appendix(),
        'website'        => 'https://devilshakerz.com/',
        'author'         => 'Tomasz \'Devilshakerz\' Mlynski',
        'authorsite'     => 'https://devilshakerz.com/',
        'version'        => '1.2',
        'codename'       => 'dvz_secure_content',
        'compatibility'  => '18*',
    ];
}

function dvz_secure_content_install()
{
    global $db;

    // database changes
    $db->modify_column('users', 'avatar', "VARCHAR(255) NOT NULL DEFAULT ''");

    if (!$db->field_exists('avatar_original', 'users')) {
        $db->add_column('users', 'avatar_original', "VARCHAR(255) NOT NULL DEFAULT ''");
    }
}

function dvz_secure_content_uninstall()
{
    global $db;

    // database
    if ($db->field_exists('avatar_original', 'users')) {
        $proxyUrls = $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "avatar_original != ''"),
            'n'
        );

        if ($proxyUrls == 0) {
            $db->drop_column('users', 'avatar_original');
        }
    }

    // settings
    $settingGroupId = $db->fetch_field(
        $db->simple_select('settinggroups', 'gid', "name='dvz_secure_content'"),
        'gid'
    );

    $db->delete_query('settinggroups', 'gid=' . $settingGroupId);
    $db->delete_query('settings', 'gid=' . $settingGroupId);

    rebuild_settings();
}

function dvz_secure_content_activate()
{
    global $mybb, $db;

    // settings
    $query = $db->simple_select('settinggroups', 'gid', "name='dvz_secure_content'");

    if ($db->num_rows($query) == 0) {
        $settingGroupId = $db->insert_query('settinggroups', [
            'name'        => 'dvz_secure_content',
            'title'       => 'DVZ Secure Content',
            'description' => 'Settings for DVZ Secure Content.',
        ]);
    } else {
        $settingGroupId = $db->fetch_field($query, 'gid');
    }

    $settings = [
        [
            'name'        => 'dvz_sc_filter_insecure_images',
            'title'       => 'Filter non-HTTPS MyCode images',
            'description' => 'Prevent displaying non-HTTPS MyCode images by replacing them with links.',
            'optionscode' => 'onoff',
            'value'       => '1',
        ],
        [
            'name'        => 'dvz_sc_block_insecure_avatars',
            'title'       => 'Block non-HTTPS avatars',
            'description' => 'Require remote avatars to be linked to over HTTPS.',
            'optionscode' => 'onoff',
            'value'       => '1',
        ],
        [
            'name'        => 'dvz_sc_proxy',
            'title'       => 'Image proxy',
            'description' => 'Forward resource requests to a proxy server.',
            'optionscode' => 'onoff',
            'value'       => '0',
        ],
        [
            'name'        => 'dvz_sc_proxy_scheme',
            'title'       => 'Image proxy URL scheme',
            'description' => 'Template used to construct the resulting resource URL. You can use <b>{PROXY_URL}</b>, <b>{URL}</b>, and <b>{DIGEST}</b>.',
            'optionscode' => 'text',
            'value'       => '{PROXY_URL}{DIGEST}/{URL}',
        ],
        [
            'name'        => 'dvz_sc_proxy_url',
            'title'       => 'Image proxy URL',
            'description' => 'Image proxy URL containing the protocol, domain and a trailing slash. Used for removing and blocking insecure resources.',
            'optionscode' => 'text',
            'value'       => 'https://domain.example/',
        ],
        [
            'name'        => 'dvz_sc_proxy_key',
            'title'       => 'Image proxy key',
            'description' => 'Key to be used when creating the URL digest.',
            'optionscode' => 'text',
            'value'       => '',
        ],
        [
            'name'        => 'dvz_sc_proxy_digest_algorithm',
            'title'       => 'Image proxy digest algorithm',
            'description' => 'Algorithm used in digest generation.',
            'optionscode' => 'text',
            'value'       => 'sha1',
        ],
        [
            'name'        => 'dvz_sc_proxy_url_protocol',
            'title'       => 'Image proxy forwarded URL protocol',
            'description' => 'Modifies the protocol part of the forwarded URL.',
            'optionscode' => 'select
raw=No changes
relative=Protocol-relative form
strip=Strip protocol
',
            'value'       => 'raw',
        ],
        [
            'name'        => 'dvz_sc_proxy_url_encoding',
            'title'       => 'Image proxy forwarded URL encoding',
            'description' => 'Encodes the URL of requested image in given format.',
            'optionscode' => 'select
raw=No encoding
percent=Percent-encoding (urlencode)
rfc1738=RFC 1738 (rawurlencode)
hex=Hex encoding
base64=base64 encoding
base64url=base64url encoding
',
            'value'       => 'hex',
        ],
        [
            'name'        => 'dvz_sc_proxy_images',
            'title'       => 'Image proxy policy',
            'description' => 'Forward selected types of images in user content through the proxy server.',
            'optionscode' => 'select
all=All images (HTTP & HTTPS)
insecure=Insecure images only (HTTP)
none=Don\'t forward images',
            'value'       => 'all',
        ],
        [
            'name'        => 'dvz_sc_proxy_avatars',
            'title'       => 'Proxy avatars',
            'description' => 'Forward selected types of images in user avatars through the proxy server.',
            'optionscode' => 'select
all=All images (HTTP & HTTPS)
insecure=Insecure images only (HTTP)
none=Don\'t forward images',
            'value'       => 'all',
        ],
        [
            'name'        => 'dvz_sc_proxy_exception_hosts',
            'title'       => 'Proxy hostname exceptions',
            'description' => 'Domains (one per line) from which HTTPS resources will not be forwarded through the proxy.',
            'optionscode' => 'textarea',
            'value'       => '',
        ],
    ];

    $i = 1;

    foreach ($settings as $setting) {
        $insertArray = $setting;

        array_walk($insertArray, function (&$value) use ($db) {
            $value = $db->escape_string($value);
        });
        $insertArray['gid']       = (int)$settingGroupId;
        $insertArray['disporder'] = (int)$i++;

        if (isset($mybb->settings[ $setting['name'] ])) {
            unset($insertArray['value']);
            $db->update_query('settings', $insertArray, "name = '" . $db->escape_string($setting['name']) . "'");
        } else {
            $db->insert_query('settings', $insertArray);
        }
    }

    rebuild_settings();
}

function dvz_secure_content_is_installed()
{
    global $db;

    // manual check to avoid caching issues
    $query = $db->simple_select('settinggroups', 'gid', "name='dvz_secure_content'");
    return (bool)$db->num_rows($query);
}

class dvz_sc
{
    const URL_UNKNOWN = 2;

    const POLICY_BLOCK = 2;
    const POLICY_PROXY = 4;
    const POLICY_PASS = 8;

    const RESOURCE_AVATAR = 2;
    const RESOURCE_MYCODE_IMAGE = 4;

    static $showPluginTools = false;

    static $videoEmbedServices = [
        'metacafe',
    ];

    static $originalAvatarUrl = null;

    // hooks
    static function parse_message(&$message)
    {
        global $parser;

        if (!isset($parser) || !$parser instanceof postParser || !isset($parser->options['allow_imgcode']) || $parser->options['allow_imgcode']) {
            if (self::resource_policy(self::URL_UNKNOWN, self::RESOURCE_MYCODE_IMAGE) !== self::POLICY_PASS) {
                $protocol = 'http' . (self::settings('proxy') && self::settings('proxy_images') == 'all' ? 's?' : null) . ':\/\/';
                $pattern = '/<img src="(' . $protocol . '[^<>"\']+)"((?: loading="lazy" )?(?: width="[0-9]+" height="[0-9]+")?(?: border="0")? alt="([^<>"\']+)" ?(?: style="float: (left|right);")?(?: class="mycode_img")? ?)\/>/i';

                $message = preg_replace_callback($pattern, 'self::parse_message_replace_callback', $message);
            }
        }
    }

    static function usercp_avatar_start()
    {
        global $lang, $footer;

        if (self::settings('block_insecure_avatars')) {
            $lang->load('dvz_secure_content');

            $lang->avatar_url_note .= $lang->dvz_sc_avatars_https_only;

            // client-side form validation (simplified match for e-mail addresses and ^https:// URLs)
            $footer .= PHP_EOL . '<script>$("input[name=\'avatarurl\']").attr("pattern", ".+@.+|https://.+")</script>';
        }
    }

    static function usercp_avatar_intermediate()
    {
        global $mybb, $avatarurl;

        // insert original URL for the form after the display URL has been fetched
        if (!empty($mybb->user['avatar_original'])) {
            $avatarurl = $mybb->user['avatar_original'];
        }
    }

    static function usercp_do_avatar_start()
    {
        global $mybb;

        if (!empty($mybb->input['remove'])) {
            // clean up the backup field
            self::$originalAvatarUrl = '';
        // verify the core is going to process a remote avatar
        } elseif (
            empty($_FILES['avatarupload']['name']) &&
            $mybb->settings['allowremoteavatars']
        ) {
            $avatarUrl = trim($mybb->get_input('avatarurl'));

            // treat Gravatar images as ordinary ones to proxy the URLs
            $avatarUrl = self::gravatar_email_to_url($avatarUrl);

            switch (self::resource_policy($avatarUrl, self::RESOURCE_AVATAR)) {
                case self::POLICY_BLOCK:
                    $mybb->input['avatarurl'] = ''; // force core to reject the request
                    break;
                case self::POLICY_PROXY:
                    // inject a proxied URL before the resource is fetched
                    $mybb->input['avatarurl'] = self::proxy_url($avatarUrl);

                    // cancel request if data too long for storage
                    if (mb_strlen($mybb->input['avatarurl']) > 255) {
                        $mybb->input['avatarurl'] = ''; // force core to reject the request
                    } else {
                        self::$originalAvatarUrl = $avatarUrl;
                    }
                    break;
                case self::POLICY_PASS:
                    self::$originalAvatarUrl = '';
                    break;
            }
        }
    }

    static function usercp_do_avatar_end()
    {
        global $mybb, $db;

        // reflect changes in the backup field / clean up
        if (self::$originalAvatarUrl !== null) {
            $db->update_query('users', [
                'avatar_original' => self::$originalAvatarUrl ? self::$originalAvatarUrl : '',
            ], "uid=" . (int)$mybb->user['uid']);
        }
    }

    static function modcp_do_editprofile_end()
    {
        global $mybb, $db, $user;

        // clean up the backup field
        if (!empty($mybb->input['remove_avatar'])) {
            if (!empty($user['avatar_original'])) {
                $db->update_query('users', [
                    'avatar_original' => '',
                ], "uid=" . (int)$user['uid']);
            }
        }
    }

    static function admin_user_users_edit_graph()
    {
        global $user;

        // insert original URL for the form
        if (!empty($user['avatar']) && !empty($user['avatar_original'])) {
            echo '<script>$("#avatar_url").val("' . htmlspecialchars_uni($user['avatar_original']) . '");</script>';
        }
    }

    static function admin_user_users_edit()
    {
        global $mybb, $user;

        if (!empty($mybb->input['remove_avatar'])) {
            self::$originalAvatarUrl = '';
        }

        // verify the core is going to process a new remote avatar
        if (
            empty($_FILES['avatar_upload']['name']) &&
            $mybb->settings['allowremoteavatars'] &&
            !empty($mybb->input['avatar_url']) &&
            !in_array($mybb->input['avatar_url'], [$user['avatar'], $user['avatar_original']])
        ) {
            $avatarUrl = trim($mybb->get_input('avatar_url'));

            // treat Gravatar images as ordinary ones to proxy the URLs
            $avatarUrl = self::gravatar_email_to_url($avatarUrl);

            switch (self::resource_policy($avatarUrl, self::RESOURCE_AVATAR)) {
                case self::POLICY_BLOCK:
                    $mybb->input['avatar_url'] = ''; // force core to ignore change
                    break;
                case self::POLICY_PROXY:
                    // inject a proxied URL before the resource is fetched
                    $mybb->input['avatar_url'] = self::proxy_url($avatarUrl);

                    // cancel request if data too long for storage
                    if (mb_strlen($mybb->input['avatar_url']) > 255) {
                        $mybb->input['avatar_url'] = '';
                    } else {
                        self::$originalAvatarUrl = $avatarUrl; // force core to ignore change
                    }
                    break;
                case self::POLICY_PASS:
                    self::$originalAvatarUrl = '';
                    break;
            }
        }
    }

    static function admin_user_users_edit_commit_start()
    {
        global $extra_user_updates;

        if (self::$originalAvatarUrl !== null) {
            // reflect changes in the backup field
            $extra_user_updates['avatar_original'] = self::$originalAvatarUrl;
        }
    }

    static function admin_config_settings_begin()
    {
        global $lang;

        $lang->load('dvz_secure_content');
    }

    static function admin_config_plugins_begin()
    {
        global $mybb, $lang;

        self::$showPluginTools = TRUE;

        $lang->load('dvz_secure_content');

        $tasks = [
            'embed_templates' => [
                'controller' => function () {
                    self::replace_embed_templates(TRUE);
                },
            ],
            'embed_templates_revert' => [
                'controller' => function () {
                    self::replace_embed_templates(false);
                },
            ],
            'replace_gravatar' => [
                'controller' => function () {
                    self::replace_gravatar_avatars();
                },
            ],
            'remove_insecure_avatars' => [
                'controller' => function () {
                    self::remove_insecure_avatars();
                },
            ],
            'proxy_avatar_urls' => [
                'controller' => function () {
                    self::proxy_avatar_urls();
                },
            ],
            'restore_proxy_avatar_urls' => [
                'controller' => function () {
                    self::restore_proxy_avatar_urls();
                },
            ],
        ];

        $taskName = $mybb->get_input('dvz_sc_task');

        if ($taskName && array_key_exists($taskName, $tasks) && verify_post_check($mybb->get_input('my_post_key'))) {
            $tasks[$taskName]['controller']();
            flash_message($lang->{'dvz_sc_task_' . $taskName . '_message'}, 'success');
            admin_redirect('index.php?module=config-plugins');
        }
    }

    static function admin_settings_print_peekers($peekers)
    {
        $peekerSettings = [
            'dvz_sc_proxy_scheme',
            'dvz_sc_proxy_url',
            'dvz_sc_proxy_key',
            'dvz_sc_proxy_digest_algorithm',
            'dvz_sc_proxy_url_protocol',
            'dvz_sc_proxy_url_encoding',
            'dvz_sc_proxy_images',
            'dvz_sc_proxy_avatars',
        ];

        return array_merge($peekers, [
            'new Peeker($(".setting_dvz_sc_proxy"), $("#row_setting_' . implode($peekerSettings, ', #row_setting_') . '"), 1, TRUE);',
        ]);
    }

    // tasks
    static function replace_embed_templates($secureMode)
    {
        require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

        foreach (self::$videoEmbedServices as $service) {
            $skipMatching = false;

            switch ($service) {
                // replace embeds with link
                case 'metacafe':
                    $replace = [
                        '<iframe src="http://www.metacafe.com/embed/{$id}/" width="440" height="248" allowFullScreen frameborder=0></iframe>',
                        '<a href="http://www.metacafe.com/fplayer/{$id}/{$title}.swf">[metacafe.com/...]</a>',
                    ];
                    $skipMatching = TRUE;
                    break;

                // set embeds protocol-relative
                default:
                    $replace = [
                        '"http://',
                        '"//',
                    ];

            }

            if (!$secureMode) {
                $replace = array_reverse($replace);
            }

            find_replace_templatesets('video_' . $service . '_embed', '#' . ($skipMatching ? '^.*$' : preg_quote($replace[0])) . '#', $replace[1]);
        }
    }

    static function replace_gravatar_avatars()
    {
        global $db;

        return $db->write_query("UPDATE " . TABLE_PREFIX . "users SET avatar = REPLACE(LOWER(avatar), 'http://www.gravatar.com/', 'https://gravatar.com/')");
    }

    static function remove_insecure_avatars()
    {
        global $db;

        return $db->update_query(
            'users',
            [
                'avatar'           => '',
                'avatartype'       => '',
                'avatardimensions' => '',
            ],
            "LOWER(avatar) LIKE 'http://%'"
        );
    }

    static function proxy_avatar_urls()
    {
        global $db;

        $query = $db->simple_select('users', 'uid,avatar,avatar_original', "
            avatar != '' AND
            (LOWER(avatar) LIKE 'http://%' OR LOWER(avatar) LIKE 'https://%') AND
            avatar NOT LIKE '" . $db->escape_string_like(self::settings('proxy_url')) . "%'
        ");

        while ($row = $db->fetch_array($query)) {
            if (mb_strpos($row['avatar_original'], 'http://') === 0 || mb_strpos($row['avatar_original'], 'https://') === 0) {
                $originalUrl = $row['avatar_original'];
            } else {
                $originalUrl = $row['avatar'];
            }

            if (mb_strpos($originalUrl, self::settings('proxy_url')) === 0) {
                $proxiedUrl = $originalUrl;
            } else {
                $proxiedUrl = self::proxy_url($originalUrl);
            }

            if (mb_strlen($proxiedUrl) > 255) {
                $db->update_query('users', [
                    'avatar' => '',
                    'avatar_original' => $originalUrl,
                    'avatartype' => '',
                ], "uid=" . (int)$row['uid']);
            } else {
                $db->update_query('users', [
                    'avatar' => $proxiedUrl,
                    'avatar_original' => $originalUrl,
                    'avatartype' => 'remote',
                ], "uid=" . (int)$row['uid']);
            }
        }
    }

    static function restore_proxy_avatar_urls()
    {
        global $db;

        return $db->write_query("UPDATE " . $db->table_prefix . "users SET avatar=avatar_original, avatar_original='', avatartype='remote' WHERE avatar_original != ''");
    }

    // core
    static function parse_message_replace_callback($matches)
    {
        list($matchFull, $matchUrl, $matchParameters, $matchAltText) = $matches;

        switch (self::resource_policy($matchUrl, self::RESOURCE_MYCODE_IMAGE)) {
            case self::POLICY_PROXY:
                $proxiedUrl = self::proxy_url($matchUrl);

                $customParameters = ' data-original-url="' . htmlspecialchars_uni($matchUrl) . '"';

                $replacement = '<img src="' . $proxiedUrl . '"' . $customParameters . $matchParameters . '/>';

                break;
            case self::POLICY_BLOCK:
                if (isset($GLOBALS['parser']) && $GLOBALS['parser'] instanceof postParser) {
                    $parser = $GLOBALS['parser'];
                } else {
                    $parser = new postParser;
                    $parser->options = [
                        'allow_mycode' => 0,
                        'allow_smilies' => 0,
                        'allow_imgcode' => 0,
                        'allow_html' => 0,
                        'filter_badwords' => 0,
                    ];
                }

                $replacement = $parser->mycode_parse_url($matchUrl, $matchAltText);

                break;
            case self::POLICY_PASS:
            default:
                $replacement = $matchFull;
        }

        return $replacement;
    }

    static function existing_avatars_secure()
    {
        global $db;

        return $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "avatar LIKE 'http://%'"),
            'n'
        ) == 0;
    }

    static function existing_avatars_proxied()
    {
        global $db;

        if (!self::settings('proxy_url')) {
            return false;
        }

        return $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "
                avatar != '' AND
                (LOWER(avatar) LIKE 'http://%' OR LOWER(avatar) LIKE 'https://%') AND
                avatar NOT LIKE '" . $db->escape_string_like(self::settings('proxy_url')) . "%'
            "),
            'n'
        ) == 0;
    }

    static function embed_templates_secure()
    {
        global $db;

        $templatesetsInUse = [];

        $query = $db->simple_select('themes', 'properties', "tid > 1");

        while ($theme = $db->fetch_array($query)) {
            $properties = my_unserialize($theme['properties']);
            $templatesetsInUse[] = (int)$properties['templateset'];
        }

        $templatesetsInUse = array_unique($templatesetsInUse);

        $numTemplatesetsInUse = count($templatesetsInUse);

        $templatesFound = 0;

        if ($numTemplatesetsInUse > 0) {
            $titles = self::$videoEmbedServices;

            array_walk($titles, function (&$title) use ($db) {
                $title = "'" . $db->escape_string('video_' . $title . '_embed') . "'";
            });

            $query = $db->simple_select('templates', 'title,template,sid', "title IN (" . implode(',', $titles) . ") AND sid IN (" . implode(',', $templatesetsInUse) . ")");

            while ($template = $db->fetch_array($query)) {
                if (preg_match('#(?<!<a href=")http://#', $template['template'])) {
                    return false;
                }

                $templatesFound++;
            }

            if ($templatesFound == count(self::$videoEmbedServices) * $numTemplatesetsInUse) {
                return TRUE;
            }
        }

        return false;
    }

    static function resource_policy($url, $type)
    {
        if ($url === self::URL_UNKNOWN) {
            $urlSecure = false; // assume worst-case scenario
        } else {
            $urlSecure = self::is_secure_url($url);
        }

        if (
            $urlSecure === false &&
            (
                (
                    $type === self::RESOURCE_AVATAR &&
                    self::settings('block_insecure_avatars') == TRUE
                ) ||
                (
                    $type === self::RESOURCE_MYCODE_IMAGE &&
                    self::settings('filter_insecure_images') == TRUE
                )
            )
        ) {
            return self::POLICY_BLOCK;
        } elseif (
            self::settings('proxy') == TRUE &&
            (
                (
                    $type == self::RESOURCE_AVATAR &&
                    (
                        self::settings('proxy_avatars') == 'all' ||
                        (
                            self::settings('proxy_avatars') == 'insecure' &&
                            $urlSecure === false
                        )
                    )
                ) ||
                (
                    $type == self::RESOURCE_MYCODE_IMAGE &&
                    (
                        self::settings('proxy_images') == 'all' ||
                        (
                            self::settings('proxy_images') == 'insecure' &&
                            $urlSecure === false
                        )
                    )
                )
            ) &&
            (
                $urlSecure === false ||
                self::secure_url_proxy_exempt($url) === false
            )
        ) {
            return self::POLICY_PROXY;
        } else {
            return self::POLICY_PASS;
        }
    }

    static function is_secure_url($url)
    {
        return mb_strpos($url, 'https://') === 0;
    }

    static function secure_url_proxy_exempt($url)
    {
        $exceptions = self::get_proxy_host_exceptions();

        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost === false || $urlHost === null) {
            return false;
        } else {
            foreach ($exceptions as $exceptionHost) {
                if ($urlHost === $exceptionHost) {
                    return TRUE;
                }
            }

            return false;
        }
    }

    static function get_proxy_host_exceptions()
    {
        static $exceptions;

        if ($exceptions === null) {
            $exceptions = array_filter(
                preg_split("/\\r\\n|\\r|\\n/", self::settings('proxy_exception_hosts'))
            );
        }

        return $exceptions;
    }

    static function proxy_url($url)
    {
        if (mb_strpos($url, self::settings('proxy_url')) !== 0) {
            $passedUrl = $url;

            switch (self::settings('proxy_url_protocol')) {
                case 'strip':
                    if (mb_strpos($passedUrl, 'http://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 7);
                    } elseif (mb_strpos($passedUrl, 'https://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 8);
                    }
                    break;
                case 'relative':
                    if (mb_strpos($passedUrl, 'http://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 4);
                    } elseif (mb_strpos($passedUrl, 'https://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 5);
                    }
                    break;
                default:
                    $passedUrl = $url;
                    break;
            }

            switch (self::settings('proxy_url_encoding')) {
                case 'hex':
                    $passedUrl = bin2hex($passedUrl);
                    break;
                case 'percent':
                    $passedUrl = urlencode($passedUrl);
                    break;
                case 'rfc1738':
                    $passedUrl = rawurlencode($passedUrl);
                    break;
                case 'base64':
                    $passedUrl = base64_encode($passedUrl);
                    break;
                case 'base64url':
                    $passedUrl = rtrim(strtr(base64_encode($passedUrl), '+/', '-_'), '=');
                    break;
            }

            if (self::settings('proxy_key')) {
                $digest = hash_hmac(self::settings('proxy_digest_algorithm'), $url, self::settings('proxy_key'));
            } else {
                $digest = null;
            }

            $proxyUrl = self::settings('proxy_scheme');

            $proxyUrl = str_replace('{PROXY_URL}', self::settings('proxy_url'), $proxyUrl);
            $proxyUrl = str_replace('{URL}', $passedUrl, $proxyUrl);
            $proxyUrl = str_replace('{DIGEST}', $digest, $proxyUrl);
        } else {
            $proxyUrl = $url;
        }

        return $proxyUrl;
    }

    static function gravatar_email_to_url($string)
    {
        global $mybb;

        $url = $string;

        if (filter_var($string, FILTER_VALIDATE_EMAIL) !== false) {
            $email = md5(strtolower(trim($string)));

            if (!$mybb->settings['maxavatardims']) {
                $mybb->settings['maxavatardims'] = '100x100';
            }

            $dimensions = explode('x', my_strtolower($mybb->settings['maxavatardims']));
            $maxwidth = reset($dimensions);
            $s = '?s=' . $maxwidth;

            $url = 'https://www.gravatar.com/avatar/' . $email . $s;
        }

        return $url;
    }

    static function description_appendix()
    {
        global $mybb, $lang;

        $content = null;

        if (self::$showPluginTools) {
            $avatarsSecure = self::existing_avatars_secure();
            $avatarsProxied = self::existing_avatars_proxied();
            $hostnameExceptionsCount = count(self::get_proxy_host_exceptions());
            $embedsSecure = self::embed_templates_secure();

            if ($hostnameExceptionsCount !== 0) {
                $hostnameExceptionsNote = ' (' . $lang->sprintf(
                    $lang->dvz_sc_hostname_exceptions,
                    $hostnameExceptionsCount
                ) . ')';
            } else {
                $hostnameExceptionsNote = null;
            }

            $controls = [
                'dynamic' => [
                    'name' => $lang->dvz_sc_controls_dynamic . $hostnameExceptionsNote,
                    'controls' => [
                        [
                            'title'  => $lang->dvz_sc_status_images,
                            'status' => self::settings('filter_insecure_images') || (self::settings('proxy') && self::settings('proxy_images') != 'none'),
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_avatars,
                            'status' => self::settings('block_insecure_avatars') || (self::settings('proxy') && self::settings('proxy_avatars') != 'none'),
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_proxy_all,
                            'status' => self::settings('proxy') && self::settings('proxy_images') == 'all' && self::settings('proxy_avatars') == 'all',
                        ],
                    ],
                ],
                'resources' => [
                    'name' => $lang->dvz_sc_controls_resources,
                    'controls' => [
                        [
                            'title'  => $lang->dvz_sc_status_secure_avatars,
                            'status' => $avatarsSecure,
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_avatars_proxied,
                            'status' => $avatarsProxied,
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_secure_embed_templates,
                            'status' => $embedsSecure,
                        ],
                    ],
                ]
            ];

            $content .= '<br /><br />';

            foreach ($controls as $controlGroup) {
                $content .= '<strong>' . $controlGroup['name'] . '</strong><br />';

                foreach ($controlGroup['controls'] as $control) {
                    $text = $control['title'] . ': ' . ($control['status'] ? $lang->dvz_sc_status_yes : $lang->dvz_sc_status_no);
                    $content .= ' <span style="display: inline-block; margin: 2px 0; padding: 4px; background-color:' . ($control['status'] ? 'mediumseagreen' : 'lightslategray') . '; font-size: 9px; color: #FFF">' . $text . '</span>';
                }
                $content .= '<br />';
            }

            $taskLinks = [];

            if (!$embedsSecure) {
                $taskLinks[] = 'embed_templates';
            } else {
                $taskLinks[] = 'embed_templates_revert';
            }

            if (!$avatarsSecure) {
                $taskLinks[] = 'replace_gravatar';
                $taskLinks[] = 'remove_insecure_avatars';
            }

            if (self::settings('proxy') && self::settings('proxy_avatars') != 'none') {
                $taskLinks[] = 'proxy_avatar_urls';
            }

            $taskLinks[] = 'restore_proxy_avatar_urls';

            foreach ($taskLinks as $taskName) {
                $url = 'index.php?module=config-plugins&amp;dvz_sc_task=' . $taskName . '&amp;my_post_key=' . $mybb->post_code;
                $title = $lang->{'dvz_sc_task_' . $taskName};
                $content .= '<br />&bull; <a href="' . $url .'"><strong>' . $title . '</strong></a>';
            }

            $content .= '<br />';
        }

        return $content;
    }

    static function settings($name)
    {
        global $mybb;

        return $mybb->settings['dvz_sc_' . $name];
    }

}
