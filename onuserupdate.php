<?php
// no direct access
defined('_JEXEC') or die('Restricted access to this plugin');

jimport( 'joomla.plugin.plugin' );
//jimport( 'joomla.form.form' );
//jimport( 'joomla.event.plugin' );

class plgUserOnuserupdate extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */



	function onUserBeforeSave()
	{
		global $old_email;
		$user =& JFactory::getUser();
		$old_email = $user->email;
	}





	function onUserAfterSave()
	{
		global $old_email;
		$plugin = JPluginHelper::getPlugin('user', 'onuserupdate');
		$params = new JRegistry($plugin->params);
		$user =& JFactory::getUser();
		$name = $user->name;
		$userID = $user->get('id');
		$db = & JFactory::getDBO();
		$query = "SELECT ".$db->nameQuote('email')." FROM ".$db->nameQuote('#__users')." WHERE ".$db->nameQuote('id')." = ".$db->quote($userID).";";
		$db->setQuery($query);
		$new_email = $db->loadResult();
		$to = $params->get('to');
		$from = 'From: ' . $params->get('frommesg') . "<" . $params->get('from') . ">" . "\r\n";
		$cc = 'Cc: ' . $params->get('cc') . "\r\n";
		$bcc = 'Cc: ' . $params->get('bcc') . "\r\n";
		$headers = $from . $cc . $bcc;
		$subject = "Email Updated by ". $name;
		$message = $name . " has updated their email address in Joomla\r\n\r\nfrom: " . $old_email . "\r\nto: " . $new_email . "\r\n\r\nThe contacts details will need updating manually in the CRM. You will also need to check in the CRM for duplicates and update any non-CRM records you may have elsewhere.";



		if ($old_email != $new_email) {
			mail($to,$subject,$message,$headers);
			$app = JFactory::getApplication();
			//$logout = $app->logout();
			JFactory::getApplication()->enqueueMessage('You have updated your email to ' . $new_email . ' Please re-login for the changes to take place.');
		}


	}

}
