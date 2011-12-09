<?php
/**
 * The application configuration file contains defaults that must be set up 
 * prior to installation of the application. It is advisable to leave most of 
 * these at their defaults except for db.dev, db.staging and db.production. 
 * Each of these should be configured to the appropriate database environment. 
 * 
 * We utilize RedBean as our ORM layer to ensure proper PDO support. Therefore, 
 * this system should be compatible with any RedBean supported database system.
 */
return array(

    'db' => array(
    		
    		/**
    		 * The current database that should be used.
    		 */
    		'active' => 'db.production',
    
    		/**
    		 * This is the development environment information. Generally leave this 
    		 * at its default.
    		 */
        'dev' => array(
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'name' => 'outliner'
        ),
        
        /**
         * This would be your QA server
         */
        'staging' => array(
            'host' => 'localhost',
            'user' => '',
            'pass' => '',
            'name' => 'outliner'
        ),
        
        /**
         * These are the database settings for when your application is in 
         * production.
         */
        'production' => array(
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'name' => 'outliner'
        ),
    ),
    
    /**
     * Environment variables. These may need to be changed if your application 
     * is not running as expected 
     */
    'env' => array(
    
    	/**
    	 * All time will be calculated relative to this time zone. We are manually 
    	 * setting the timezone to ensure that even a malconfigured .ini will 
    	 * not break things. 
    	 */
    	'timezone' => 'America/Toronto',
    	
	    /**
	    * Name of the session - you can change this to whatever you want.
	    */
      'session' => 'outliner-prod',
    ),

    
    /**
     * Application specific settings. Do not change these unless you know what 
     * you are doing.
     */
    'app' => array(
    	'version' => '0.5.4',
    	'mode' =>0,
			'theme' => 'default',
			'path' => array(
				'resource' => 'view/resources/',
				'view' => 'view/theme/',
		    'controller' => 'controller/',
		    'model' => 'model/'
			)
	    
    )
);
?>
