<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package phpfreechat
 * @subpackage setup
 * @version $Id: tables_update.inc.php 31898 2010-09-05 17:52:00Z ralfbecker $
 */

function phpfreechat_upgrade1_6_001()
{
	return $GLOBALS['setup_info']['phpfreechat']['currentver'] = '1.8';
}
