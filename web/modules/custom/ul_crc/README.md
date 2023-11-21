INTRODUCTION
------------

The UL CRC module provides a service to integrate into UL's Commercial Resource Center API. 


INSTALLATION
------------
 
 * Install as you would normally install a contributed Drupal module.

   
CONFIGURATION
-------------

* Configure connection and caching information in Administration » Configuration » Services > UL CRC:
  
HOW TO USE
------------
 
In your module use this like any other Drupal service. 

```
$crc = \Drupal::service('ul_crc'); 

// Search CRC by keyword and page number. 
$crc->search('keyword', 1); 

// Search CRC for a specific asset. 
$crc->getAsset(123); 
```
