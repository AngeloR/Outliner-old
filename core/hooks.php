<?php

d('Loaded : core/hooks.php');
/**
 * 
 * This class manages all those scallywag plugins. 
 * 
 * Basically, Captain_Hook manages all hooks in the system. Plugins will only 
 * ever need to take advantage of the register/unregister hook to hook into the 
 * system. 
 * 
 * @author SupportCon
 *
 */
class Captain_Hook {
	
	public function __construct() {
		
	}
	
	/**
	 * 
	 * Execute all callbacks assigned to a particular hook. 
	 * 
	 * @param string $hook
	 */
	public static function execute($hook, &$args) {
		$hooks = R::find('hook','name = ?',array($hook));

		foreach($hooks as $id => $hook) {
			if(Plugin_Manager::load($hook->plugin)) {
				switch($hook->mode) {
					
					case 'function':
						d('Executing Hook: '.$method.'()');
						$hook->method(&$args);
						break;
					
					case 'static':
						d('Executing Hook: '.$hook->object.'::'.$hook->method.'()');
						call_user_func($hook->object.'::'.$hook->method, &$args);
						break;
					
					case 'object':
						$obj = new $hook->object();
						$method = $hook->method;
						d('Executing Hook: '.$hook->object.'->'.$method.'()');
						$obj->$method(&$args);
						break;
				}
			}
			else {
				d('Hook: Loading of '.$hook->plugin.' failed!');
			}
		}
	}
	
	/**
	 * 
	 * Register a new hook with the system. New hooks can be created at any time
	 * but exist in a global namespace. Therefore, two hooks that have the same 
	 * name will end up with unknown issues. It is best practice to preface the 
	 * name of your plugin with your hook as in "pluginname-hook" 
	 * 
	 * You pass in the safename of the plugin as well as the hookname that you 
	 * are trying to create and the callback. Generally, it is best practice to 
	 * callback to an object, but a function will work as well. Note that if 
	 * the callback is an array, an attempt will be made to instantiate the 
	 * object portion before calling the method. To get around this, pass your 
	 * callback with a third key called "mode" and assign it the value "static". 
	 * 
	 * Once registered the "id" of that particular hook will be returned. You 
	 * don't need to do anything with it, just note that it is not null.
	 * 
	 * @param string $plugin_name
	 * @param string $hook_name
	 * @param array $callback Anything that is valid by is_callable encapsulated in an array: as in array('func_name');
	 */
	public function register($plugin_name, $hook_name, array $callback) {
		// Only one hook of each name for each Object::Method for each plugin.
		$hook = R::find('hook','name = ? and plugin = ?',array($hook_name,$plugin_name));

		if(empty($hook)) {
			$hook = R::dispense('hook');
			
			$hook->name = $hook_name;
			$hook->plugin = $plugin_name;
			
			// set up the callback
			if(array_key_exists('mode',$callback)) {
				$hook->object = $callback[0];
				$hook->method = $callback[1];
				$hook->mode = strtolower($callback['mode']);
			}
			else if(count($callback) == 1) {
				$hook->method = $callback[0];
				$hook->mode = 'function';
			}
			else {
				$hook->object = $callback[0];
				$hook->method = $callback[1];
				$hook->mode = 'object';
			}
			
			d('Hook: '.$hook->name.' registered to '.implode('::',$callback));
			return R::store($hook);
		}
		return current($hook);
	}
	
	/**
	 * 
	 * When you call unregister you should pass in the name of the plugin that 
	 * you want to unregister. When you do, only hooks that point to that plugin 
	 * are deleted, leaving any remaining hooks that may point to other plugins. 
	 * 
	 * These are ok since they will be cleared out if you ever uninstall that 
	 * plugin.
	 * 
	 * @param string $plugin
	 */
	public function unregister($plugin) {
		
		$hooks = R::find('hook','plugin = ?',array($plugin));
		
		if(!empty($hooks)) {
			foreach($hooks as $id => $hook) {
				R::trash($hook);
			}
		}
	}
}

/*
                                                                                                    
                                          ```                                                       
                                    `.-::::::::-.``                                                 
                                 `-//////////////::-.`                                              
                                ./+/++///++/////++////:.                  .                         
                               `/+//+///++////++////////:`               .`                         
                               .++/++//+o++//++/:::///////.             `.                          
                               .++/+oooooooooooo+++////+++/`            .`                          
                               `/o++ooo+///+oosssooo+///+++:           `.                           
                                `-+o+:``    ``-/+osso+///++/`          .`                           
                                  ```           `-+ssoo+++++.  .      `.                            
                                                  `:osoo+++o. :y-     .`                            
                                                    -osooo+o. sho    `.                             
                                                     .oooooo..hdy    .                              
                                                      -ooooo`+ddy`  `.                              
                                                      `oooo+.hddy   .`                              
                                    --.`               +oos:+ddh:  `.                               
                                   -hdhy+-.`           +os/-dmh/   -                                
                                   `ymmmmdhys+oosso/-`-ss/:ydy-   ..                                
                                    .smmmmmmmmmmmddddhhdhhdh+`   `-                                 
                                     `/hmmmmmmhhyhhhmmmmdy+.     ..                                 
                                       `/shmNy++s++smhs/-`      `-                                  
                                        .-omh//++/ooh.          -.                                  
                                       :dNNh/::/:-/:h/`        `-                                   
                                     .+dNNNs+//+o+//mm-        -.                                   
                                    .dNNNNNh/:/+o+/+NNy-`     .:                                    
                                  .ohmmNNNNNh+:----+MNNmh:   `:.                                    
                                  /mNNNNNNNNNNh/---+MNNNNy:` .:                                     
                                   :dddhdmNNNNNm+//+dNNNNNm+`:.                                     
                                  `oyyyyyyhdmdhys-.../yNNNNd::                                      
                                  +yyyyyyhhdddhyso.`..:+hddh+.                                      
                                 :yyyyyyhhdddhhhyso:+///:--:+/-`                                    
                                /yyyyyyyyddddhhhyyyso+/:---:/++/.                                   
                               -hyyyyyssosyhhhhhyyo/ssssso+++++/:                                   
                               shhhhhyy+/syyyhhho:.://+os+ohhdhs:                                   
                               ohddddhyyyyyhhhdh+:-:----:/+oymmddo:...```..`                        
                                `/ssshhhhhhdddmho/:::---:/+smNdddddo+::s/-.//                       
                                    `hddddyshdddhy+so++//ohmmdhdddyssos:   `o.                      
                                    `so/-`  `hddhhhhssy/oyo+//:-.oso++. ` `-+`                      
                                            /ddhhhhhssysy/       `.-.`  .::.                        
                                           +hhhhyyyhyosyshoody.                                     
                                          +hhhyyyyyyyoodoydddh-                                     
                                         /hhhyyyyyyyyoodsshdy.                                      
                                        -hhhyyyyyyyyyooydoyy.                                       
                                       `ydhyyyssyyyyyo+smssy-                                       
                                       /dhhyysssyyyyys+omhoyo                                       
                                      .yhhyyysssyyyyys+odmosy.                                      
                                      /hhhyyssssyyyhyyoodmysy/                                      
                                     .yhhyyyssssyyyhyyoohmhoys                                      
                                     ohhhyyyssssyyyhhyooymmoyy.                                     
                                    :hhhyyyssssssyyhhysosmmosy/                                     
                                   .yhhhyyyssssssyyhhys+oyhssyy.                                    
                                  `shhhyyyyssssssyyhhyy++``+oyh+                                    
                                   :oyyyyyyssssssyyhhhy//` :+yyo`                                   
                                     `-:+syssssssyyhhhy+/` .::.`                                    
                                        `+dhhho+hhhddd+.`                                           
                                      `.oddhhy-+dmmmmh`                                             
                                      /yydhhdo:ssyyso/                                              
                                      :o//++o/:ssso+/.                                              
                                      `//:::/.-sso+/:                                               
                                       :/::/: .sso+/o-`                                             
                                       -/:::` `sso/oNmdo.                                           
                                 `     ./::`  `sso+mNmmm+                                           
                                 o/    .//.   `ss+hNmdho`                                           
                                 /d+.`./os/+` :ysyNdo-`                                             
                                  -shhdmmmy- -mNNNmys/`                                             
                                    .shhmy`  +mNNNNNmmhso/:.`                                       
                                    -o+ohm.  `smm+sdmNNmmmmmhs.                                     
                                   -dmdmNs     .-` `:oyyssoo+/`                                     
                                  `yddmmNo                                                          
                                  ommmmmm+                                                          
                                 `hmmmhs/`                                                          
                                  ./:`                                                              
                                                                                                    
                                                                                                    
                                                                                                    
*/