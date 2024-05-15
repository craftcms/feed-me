# Troubleshooting Tips

### Performance

If you're experiencing slow processing for your feed, try the following:

- Turn off `devMode`. Craft's built-in logging when devMode is switched on will greatly slow down the import process, and causes a high degree of memory overhead.
- Similarly, when importing, make sure to disable the debug toolbar under your user account preferences. It can cause high memory overhead and other resource related issues.
- Consider turning on the `compareContent` [configuration setting](get-started/configuration.md#configuration-options) to prevent unnecessary content overwriting.
- Consider selecting the Add Entries option for duplication handling, depending on your requirements.
- Consider turning off the Backup option for the feed. This will depend on your specific circumstances.
- Opt for a JSON feed - there is significantly less processing overhead as opposed to XML.

You may also need to adjust the `memory_limit` and `max_execution_time` values in your php.ini file if you run into memory issues.

### Unexpected Results

If you're experiencing unexpected results when running an import, try to isolate the issue by selectively mapping fields until you have a bare-minimum import.

For example, if you're mapping 20+ fields for an Entry import, but it isn't working, try to map just the Title field, and work your way through mapping additional fields until things stop working as expected.

### Logging

Feed Me create a log event for just about everything it does, including errors and other status information. If you're experiencing issues or unexpected results with a Feed, consult the **Logs** tab first.

![The Logs tab](./screenshots/feedme-logs.png)

### Debugging

Feed Me includes a special view to assist with debugging your feed, should you encounter issues or errors during an import. With [devMode](https://craftcms.com/docs/config-settings#devMode) enabled, click the “gear” in the problematic feed’s row to expand its utility drawer, then click **Debug**.

![Feedme Overview](./screenshots/feedme-overview.png)

Debug output will be a combination of [`print_r`](https://www.php.net/manual/en/function.print-r.php)-formatted objects and log messages, providing you with as much information as possible about your feed settings, field-mappings, and data. If exceptions occur while processing the feed, they’ll appear on this page, too.

::: warning
Debugging a feed attempts to actually run the import, so make sure you have [backups](./feature-tour/creating-your-feed.md#backup) on, or are working in a disposable environment!
:::
