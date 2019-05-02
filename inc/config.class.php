<?php
/**
 * Created by PhpStorm.
 * User: Tobbi
 * Date: 2018-04-06
 * Time: 14:43
 */

class PluginChildticketmanagerConfig extends Config {
   const CONTEXT = 'plugin:childticketmanager';


   static function getTypeName($nb = 0) {
      return __("Tickets enfants", "childticketmanager");
   }

   static function getConfig() {
      return self::getConfigurationValues(self::CONTEXT);
   }

   static function initConfig() {
      return Config::setConfigurationValues(self::CONTEXT, [
         'childticketmanager_close_child'       => 0,
         'childticketmanager_resolve_child'     => 0,
         'childticketmanager_display_tmpl_link' => 0,
      ]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::createTabEntry(self::getTypeName());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::showForConfig($item, $withtemplate);
      }

      return true;
   }


   static function showForConfig() {
      if (!$canedit = Session::haveRight(self::$rightname, UPDATE)) {
         return false;
      }

      $current_config = self::getConfig();
      $current_config['childticketmanager_close_child'] = isset($current_config['childticketmanager_close_child'])
         ? $current_config['childticketmanager_close_child']
         : false;
      $current_config['childticketmanager_resolve_child'] = isset($current_config['childticketmanager_resolve_child'])
         ? $current_config['childticketmanager_resolve_child']
         : false;
      $current_config['childticketmanager_display_tmpl_link'] = isset($current_config['childticketmanager_display_tmpl_link'])
         ? $current_config['childticketmanager_display_tmpl_link']
         : false;

      if ($canedit) {
         echo "<form name='form' action='".Toolbox::getItemTypeFormURL("Config")."' method='post'>";
      }

      echo __("Fermer les tickets enfants à la fermeture du parent", 'childticketmanager');
      echo "&nbsp;";
      Dropdown::showYesNo("childticketmanager_close_child",
                          $current_config['childticketmanager_close_child']);
      echo "<br>";
      echo "<br>";

      echo __("Résoudre les tickets enfants à la résolution du parent", 'childticketmanager');
      echo "&nbsp;";
      Dropdown::showYesNo("childticketmanager_resolve_child",
                          $current_config['childticketmanager_resolve_child']);

      echo "<br>";
      echo "<br>";

      echo __("Afficher le lien vers le gabarit", 'childticketmanager');
      echo "&nbsp;";
      Dropdown::showYesNo("childticketmanager_display_tmpl_link",
                          $current_config['childticketmanager_display_tmpl_link']);

      echo "<br>";
      echo "<br>";

      if ($canedit) {
         // we define a set of hidden field to indicate to glpi, we save data for the plugin context
         echo Html::hidden('config_class', [
            'value' => __CLASS__
         ]);
         echo Html::hidden('config_context', [
            self::CONTEXT
         ]);
         echo Html::submit(_sx('button','Save'), [
            'name' => 'update'
         ]);
         Html::closeForm();
      }
   }
}
