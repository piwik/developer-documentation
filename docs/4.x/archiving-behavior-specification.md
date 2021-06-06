---
category: API Reference
---

# Archiving Behavior Specification

This page lists all expected behavior of the internal report aggregation and storing mechanism (archiving)
and the expected behavior of the `core:archive` command (the cron archiving process).
It serves as a reference since the number of cases to think about is too high to keep in ones head and as
a jumping off point for manual testing of the whole process.

It will be added to over time, since this process in Matomo is especially complex.

## Core Archiving

_Brief Description of system:_ the entry point for this is the `Piwik\Archive` class which handles both querying
for archive data, and launching the report aggregation process if archive data isn't found. `Piwik\ArchiveProcessor\Loader`
is in charge of determining if archiving should be done and then launching the whole process. `PluginsArchiver` is
the entrypoint for report aggregation.

### General Expected Behavior

API methods use the `Piwik\Archive` class to query for archive data. If we are allowed to archive in the current
request/process, and cannot find a recent, usable archive, we generate it.

### Settings

* browser triggered archiving: TODO
* today time to live: this value is the number of seconds before which an archive for today's date is considered valid. If an
  archive is older than this value, then it is considered outdated. Controlled by a UI setting and the
  `[General] time_before_today_archive_considered_outdated` INI config option. This is also used as the default value
  for archives for other periods.
* `[General] time_before_week_archive_considered_outdated`, `[General time_before_month_archive_considered_outdated]`,
  `[General] time_before_year_archive_considered_outdated`, `[General] time_before_range_archive_considered_outdated`:
  specific ttls for different period types, defaults to 'today time to live' value if not specified
* TODO

### Rules

**When archiving is allowed/disallowed for the current process**

If browser archiving is enabled, any HTTP request or CLI command can initiate archiving. Things are more complicated
if browser archiving is disabled:

- If the request is for a range period, and `[General] archiving_range_force_on_browser_request` is absent or set to 1,
  then the request will always be allowed to initiate archiving.
- If the request is triggered by the core:archive command, then it will always be allowed to initiate archiving. We assume
  that core:archive initiated the request if the `trigger` query parameter is set to `archivephp` and the user has superuser
  access.
- If we are archiving reports for a single plugin (either because it is forced during the core:archive command, or 
  the request is for a segment that isn't marked as pre-processed), then the request will always be allowed to initiate
  archiving...
  - unless, the reason we are archiving a single plugin is because the request is for a segment and
    `[General] browser_archiving_disabled_enforce` is set to 1.

**Optimizations that skip archiving**

_When there are no visits_

In certain situations, Matomo will be able to tell it does not have to archive and will skip it for a site and period.
This is generally because the site has no visits for that period, and we are able to easily tell.

The specific logic is in `Loader::canSkipThisArchive()`. If a website/period pair is:

- has no visits in the log tables for the period
- and has no subperiod archive within the period (ie, if we're looking at a week and there are no day archives for that week, this would be true)

We know we'd end up with zero visits if we archive, so we can just skip it. The only exceptions are for sites that do
not use the tracker (so there will always be zero visits in the log table) or are specified via the `'Archiving.getIdSitesToArchiveWhenNoVisits'`
event. RollUp websites are examples of sites for which this optimization does not apply.

This optimization is used both before launching the archive aggregation logic and in the `core:archive` command
before we launch individual archive requests. This saves a bit more time since we also don't have to launch an
archiving command.

### Special Query Parameter Handling

* `trigger`: if set to `archivephp`, let's the core archiving process know it was launched by the `core:archive` command.
  The archiving process will only assume this if the current request also has superuser access loaded.
* `skipArchiveSegmentToday`: if set and launching archiving is enabled for the current request, then `Archive.php` will
  skip the archiving of segment archives for today's date, if there are any in the request. (It will also skip querying this data as well.)
* TODO

### Concurrency

**Processing multiple archives at the same time**

This is not desired behavior, we prefer if only one process creates a single archive, but sometimes multiple requests
 will trigger archiving at the same time (this is only an issue for setups that enable browser triggered archiving).

When this happens, it is expected that both will aggregate the same data, and one will finish after the other, giving
it a greater `ts_archived` value. This archive is the one that ends up being used; the duplicate gets cleaned up in a
scheduled task.

**When a site is deleted during archiving**

TODO

## core:archive command

_Brief Description of system:_ The `core:archive` command is meant to be run as a cron job periodically archiving data
based on a queue. The queue that controls what archives get launched is the `archive_invalidations` table. `core:archive`
will both, insert into this table before processing each site and process the entries, launching the archiving process
for each valid entry.

### General Expected Behavior

**invalidating today & yesterday archives**

TODO

**invalidating custom ranges**

TODO

**invalidating queued invalidations**

TODO

**invalidations are inserted after archiving for a site begins**

TODO

**invalidations are inserted while archiving for a site is in progress**

TODO

**site is deleted during invalidation or archiving**

TODO

**an archiving run fails**

TODO

**an archiving run fails, sites are deleted, the archiver starts again**

TODO

**duplicate invalidations are found in the archive_invalidations table**

TODO

**how and when the ttl is checked**

TODO
(before invalidating and when processing individual invalidations. segments can be archived if visit invalidation is not respected.)

**archiving today/yesterday**

TODO
(today/yesterday in the site's timezone)

**amount of concurrency to expect**

TODO
(even w/ sharedsiteids, there can be multiple processes that process the same site (users do this))

### Re-archiving of past data for new or updated segments

When a segment is created or updated, we make sure to re-archive the last several months of data for them, so
that data will appear for users. If we didn't, the data would be archived in the yearly archive for the current day,
which means it may not be much data if the current day is close to the beginning of the year, and could also mean
a much larger archiving request than just archiving days, weeks, months, years in that order for the segment.

We do this by checking if the segment created time or updated time is newer than the last time we invalidated archives
in core:archive. If it is, we know we have missing or out of date data for a segment, and we have to re-archive.

The segment created/updated time is stored in the `segment` table. The last invalidation time is stored in the
`CronArchive.lastInvalidationTime` option.

### Important Command Line Options

`--skip-idsites`: TODO
`--skip-all-segments`: TODO
`--force-idsites`: TODO
`--force-periods`: TODO
`--skip-segments-today`: TODO

### Launching core:archive through HTTP requests

TODO

**Limitations compared to CLI**

TODO

/*
        $command->addOption('', null, InputOption::VALUE_OPTIONAL,
            'If specified, archiving will be processed only for these Sites Ids (comma separated)');
        $command->addOption('skip-segments-today', null, InputOption::VALUE_NONE,
            'If specified, segments will be only archived for yesterday, but not today. If the segment was created or changed recently, then it will still be archived for today and the setting will be ignored for this segment.');
        $command->addOption('force-periods', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month,year,range)");
        $command->addOption('force-date-last-n', null, InputOption::VALUE_REQUIRED,
            "This last N number of years of data to invalidate when a recently created or updated segment is found.", 7);
        $command->addOption('force-date-range', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD");
        $command->addOption('force-idsegments', null, InputOption::VALUE_REQUIRED,
            'If specified, only these segments will be processed (if the segment should be applied to a site in the first place).'
            . "\nSpecify stored segment IDs, not the segments themselves, eg, 1,2,3. "
            . "\nNote: if identical segments exist w/ different IDs, they will both be skipped, even if you only supply one ID.");
        $command->addOption('concurrent-requests-per-website', null, InputOption::VALUE_OPTIONAL,
            "When processing a website and its segments, number of requests to process in parallel", CronArchive::MAX_CONCURRENT_API_REQUESTS);
        $command->addOption('concurrent-archivers', null, InputOption::VALUE_OPTIONAL,
            "The number of max archivers to run in parallel. Depending on how you start the archiver as a cronjob, you may need to double the amount of archivers allowed if the same process appears twice in the `ps ex` output.", false);
        $command->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE,
            "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $command->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE,
            "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be "
            . "useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
        $command->addOption('php-cli-options', null, InputOption::VALUE_OPTIONAL, 'Forwards the PHP configuration options to the PHP CLI command. For example "-d memory_limit=8G". Note: These options are only applied if the archiver actually uses CLI and not HTTP.', $default = '');
        $command->addOption('force-all-websites', null, InputOption::VALUE_NONE, 'Force archiving all websites.');
*/