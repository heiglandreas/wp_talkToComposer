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
 * Plugin Name: Talk to Composer
 * Plugin URI: https://github.com/heiglandreas/wp_talkToComposer
 * Description: Register your currently active plugins with composer
 * Version: 1.1.2
 * Author: Andreas Heigl <a.heigl@wdv.de>
 * Author URI: http://andreas.heigl.org
 */
require_once 'src/TalkToComposer.php';
$talkToComposer = new Org_Heigl\Wordpress\Plugins\TalkToComposer();

add_action('activated_plugin', [$talkToComposer, 'activatePlugin'], 10, 2);
add_action('activate_plugin', [$talkToComposer, 'activateSelf'], 10, 2);
add_action('deactivated_plugin', [$talkToComposer, 'deactivatePlugin'], 10, 2);
add_action('switch_theme', [$talkToComposer, 'switchTheme'], 10, 2);
