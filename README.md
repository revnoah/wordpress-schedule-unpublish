# Schedule Unpublish

## A WordPress Plugin

**Schedule Unpublish** is a plugin to set a post to pending at a particular date and time

## Installing The Plugin

The plugin is stored in the build folder.

[build/schedule-unpublish.zip](build/schedule-unpublish.zip)

1. Upload the plugin files to the `/wp-content/plugins/schedule-unpublish` directory, or install the plugin through 
the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Tools->Unpublish Queue screen to view posts in the queue to be unpublished.

## Settings

The plugin adds a menu item to the Tools menu. This screen provides an easy way to see which posts are queued to be unpublished.

## Using This Plugin

To schedule posts to be unpublished, set an Unpublish Date in the sidebar.

## Contributing

Please review the [CONTRIBUTING.md](CONTRIBUTING.md) file if you are interested in helping develop or 
maintain this plugin. Also, please be aware that contributors are expected to adhere to the 
[CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) and use the [PULL_REQUEST_TEMPLATE.md](PULL_REQUEST_TEMPLATE.md) 
when submitting code.

## Development

You will need npm and phpcs to get started. Use `npm install` to install gulp and other libraries 
required to help package the plugin for publishing. Source files are in the `source` folder. The 
code style is defined in the `phpcs.xml` file and requires `phpcs` to validate the code.

## License

The WordPress plugin **Schedule Unpublish** is open-sourced software licensed under GPL-3 license.
