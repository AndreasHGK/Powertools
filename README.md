# Powertools
Assign commands to specific items

[![](https://poggit.pmmp.io/shield.state/Powertools)](https://poggit.pmmp.io/p/Powertools)
[![](https://poggit.pmmp.io/shield.api/Powertools)](https://poggit.pmmp.io/p/Powertools)

There are 2 base permissions: `powertools.use` and `powertools.command`.
You need the first permission to use powertools and the second one to assign/unassign powertools. It's that simple!

Powertools work a little different in this plugin than they do in java edition. You assign commands to **SPECIFIC** items and not the id. This way you can share powertools to people to other people (who might only have the permission to run powertools).

## API
If, for instance, you want to make this plugin compatible with your plugin (maybe a kit plugin or something) you can do that by making sure you use Powertools with `use AndreasHGK\Powertools\Powertools;` and then call it by doing this:

```php
$pt = Powertools::getInstance();

/**
* @param item $item
* @param string $command
*
* @return item $item
*/
$pt->enablePowertool($item, $command);
```

If the item is an existing item, be sure to also add a `$sender->getInventory()->setItemInHand($item)`
