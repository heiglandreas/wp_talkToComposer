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

    public function __construct()
    {
        // Check for a composer installation and if none is available, get one
    }

    public function getComposerBinary()
    {
        return 'composer';
    }


    /**
     * Activate this plugin
     *
     */
    public function activateSelf($plugin)
    {
        if (strpos(strtolower($plugin), 'talktocomposer/') !== 0) {
            return;
        }

        // Add the wpackagist repository
        exec(sprintf(
            'cd %2$s && %1$s config repositories.wpackagist composer http://wpackagist.org',
            $this->getComposerBinary(),
            ABSPATH
        ), $output, $returnVal);
    }

    /**
     * @param string  $plugin      The path to the main plugin-file
     * @param boolean $networkWide Whether the plugin was activated networkwide
     */
    public function activatePlugin($plugin, $networkWide)
    {
        $plugin = $this->getPluginName($plugin);
        exec(sprintf(
            'cd %3$s && %1$s require --no-update --no-progress wpackagist-plugin/%2$s',
            $this->getComposerBinary(),
            escapeshellarg($plugin),
            ABSPATH
        ), $output, $returnVar);

        error_log(getcwd());
        error_log(implode("\n", $output));
    }

    /**
     * @param string  $plugin      The path to the main plugin-file
     * @param boolean $networkWide Whether the plugin was deactivated networkwide
     */
    public function deactivatePlugin($plugin, $networkWide)
    {
        $plugin = $this->getPluginName($plugin);
        exec(sprintf(
            'cd %3$s && %1$s remove --no-update --no-progress wpackagist-plugin/%2$s',
            $this->getComposerBinary(),
            escapeshellarg($plugin),
            ABSPATH
        ), $output, $returnVar);

        error_log(getcwd());
        error_log(implode("\n", $output));
    }

    protected function getPluginName($pluginPath)
    {
        return dirname($pluginPath);
    }
}