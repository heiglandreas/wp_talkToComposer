<?php
/**
 * Copyright (c) 2015-2015 Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright 2015-2015 Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @since     06.11.2015
 * @link      http://github.com/heiglandreas/wp_talktocomposer
 */
namespace Org_Heigl\Wordpress\Plugins;

class TalkToComposer
{
    protected $composer = '';

    protected $absPath = '';

    public function __construct()
    {
        $this->checkForComposer();
        $this->absPath = $this->getAbsPath();
    }

    public function getComposerBinary()
    {
        return $this->composer;
    }


    /**
     * Activate this plugin
     *
     */
    public function activateSelf($plugin)
    {
        if (strpos(strtolower($plugin), 'talk-to-composer/') !== 0) {
            return;
        }

        // Add the wpackagist repository
        if (! file_exists($this->absPath . '/composer.json')) {
            file_put_contents($this->absPath . '/composer.json', '{}');
        }
        $composerJson = json_decode(file_get_contents($this->absPath . '/composer.json'), JSON_OBJECT_AS_ARRAY);
        if (! isset($composerJson['repositories'])) {
            $composerJson['repositories'] = array();
        }

        $repository = array(
            'type' => 'composer',
            'url'  => 'http://wpackagist.org',
        );

        if (! in_array($repository, $composerJson['repositories'])) {
            $this->exec(sprintf(
                'config repositories.wpackagist composer http://wpackagist.org'
            ), $output, $returnVal);
        }

        $this->exec(sprintf(
            'require org_heigl/talk_to_composer'
        ));

        // Add All currently active plugins and themes
        $command = sprintf(
            './vendor/bin/wp plugin list --status=active --field=name'
        );
        exec($command, $output, $returnVal);

        if ($returnVal != 0) {
            return;
        }

        foreach ($output as $plgn) {
            $this->exec(sprintf(
                'require --no-update --no-progress wpackagist-plugin/%1$s',
                escapeshellarg($plgn)
            ));

        }
    }

    /**
     * @param string  $plugin      The path to the main plugin-file
     * @param boolean $networkWide Whether the plugin was activated networkwide
     */
    public function activatePlugin($plugin, $networkWide)
    {
        $plugin = $this->getPluginName($plugin);
        $cmd = sprintf(
            'require --no-update --no-progress %1$s',
            escapeshellarg($plugin)
        );
        $this->exec($cmd);
    }

    /**
     * @param string  $plugin      The path to the main plugin-file
     * @param boolean $networkWide Whether the plugin was deactivated networkwide
     */
    public function deactivatePlugin($plugin, $networkWide)
    {
        $plugin = $this->getPluginName($plugin);
        $this->exec(sprintf('remove --no-update --no-progress %1$s',
            escapeshellarg($plugin)
        ));

    }

    protected function getPluginName($pluginPath)
    {
        if (! file_exists(dirname($pluginPath) . '/composer.json')) {
            return 'wpackagist-plugin/' . dirname($pluginPath);
        }

        $info = json_decode(file_GEt_contents(dirname($pluginPath) . '/composer.json'), true);

        return $info['name'];
    }

    /**
     * @param string   $new_name  Name of the new theme.
     * @param WP_Theme $new_theme WP_Theme instance of the new theme.
     *
     * @return void
     */
    public function switchTheme($themeName, $theme)
    {
        $currentTheme = basename($theme->get('ThemeURI'));
        $parentTheme = basename($theme->get('Template'));

        // Remove all current themes
        $this->exec(sprintf(
            'remove --no-update --no-progress `%1$s show -iN | grep wpackagist-theme`',
            $this->getComposerBinary()
        ));

        $themeIterator = new \DirectoryIterator(WP_CONTENT_DIR . '/themes');
        foreach ($themeIterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            if (! file_Exists($item->getPathname() . '/composer.json')) {
                continue;
            }

            $content = json_decode(file_get_contents($item->getPathname() . '/composer.json'),
                true);

            if ($content['type'] !== 'wordpress-theme') {
                continue;
            }

            $this->exec(sprintf(
                'remove --no-update --no-progress %1$s',
                escapeshellarg($content['name'])
            ));
        }

        // add the current theme to composer.json
        $this->exec(sprintf(
            'require --no-update --no-progress wpackagist-theme/%1$s',
            escapeshellarg($currentTheme)
        ));

        if (! $parentTheme) {
            return;
        }

        // Add a possible parent theme to composer.json
        $this->exec(sprintf(
            'require --no-update --no-progress wpackagist-theme/%1$s',
            escapeshellarg($parentTheme)
        ));

    }

    /**
     * Check whether composer is available or not
     *
     * If composer is not available we try to fetch our own composer installation
     *
     * @return bool
     */
    protected function checkForComposer()
    {
        $composerPath = __DIR__ . '/../bin/composer';
        exec('which composer', $output, $returnValue);
        if ($returnValue == 0) {
            $this->composer = $output[0];

            return true;
        }

        if (is_executable($composerPath)) {
            $this->composer = $composerPath;
            return true;
        }

        if (! file_Exists(dirname($composerPath))) {
            mkdir(dirname($composerPath));
        }

        $url = 'https://getcomposer.org/composer.phar';
        $fp = fopen ($composerPath, 'w+');
        $ch = curl_init(str_replace(" ","%20",$url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        chmod($composerPath, 0755);

        $this->composer = realpath($composerPath);

        return true;
    }

    protected function getAbsPath()
    {
        $absPath = ABSPATH;

        if (file_exists(dirname($absPath) . '/wp-config.php')) {
            $absPath = dirname($absPath) . '/';
        }

        return $absPath;
    }

    protected function exec($command)
    {
        $cmd = sprintf(
            'cd %1$s && COMPOSER_HOME=%2$s %3$s %4$s',
            $this->absPath,
            escapeshellarg('~/.composer'),
            $this->getComposerBinary(),
            $command
        );

        exec($cmd);
    }
}