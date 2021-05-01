<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Code fragment to define the version of the rtcproctor module
 *
 * @package    mod_rtcproctor
 * @copyright  2020 Nopparut Saelim
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var stdClass $plugin
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$plugin->component = 'mod_rtcproctor';
$plugin->version   = 2021050100; // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2020053000; // Requires this Moodle version (3.10).
// $plugin->cron      = 0; // Period for cron to check this module (secs).

// $plugin->maturity  = MATURITY_STABLE;
// $plugin->release   = "3.10.0"; // User-friendly version number.
