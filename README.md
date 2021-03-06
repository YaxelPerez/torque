# Torque

A Python web server and MediaWiki extension that work
to turn a MediaWiki instance into a CMS.

The basic workflow is that, after installation, you can upload a csv
to the torque system through MediaWiki, and then tailor the output
of that system based on what MediaWiki group a user is part of.

This document provides a brief overview, but see
[the design documentation](DESIGN.md) for more about the features.  You can also
join the
[chat channel](https://chat.opentechstrategies.com/#narrow/stream/45-Lever-for.20Change)
to talk with the development team, or reach us by filing a ticket in the
[issue tracker](https://github.com/opentechstrategies/torque/issues).

Examples of a torque-compatible proposal input pipeline for different
use cases, with ansible scripts relating to setting up a function system
can be seen in the
[torque-sites](https://github.com/opentechstrategies/torque-sites) repository.

[Example Setup](./EXAMPLE.md)

# torquedata

torquedata is a Flask server that's responsible for storing spreadsheet data
and then serving it out as needed.  As a rule, it's very accepting and should
not be exposed to the greater internet.  All of the authentication and authorization
is done via the MediaWiki plugin.

Data, indices, and configuration are stored in the filesystem.

See [torquedata README](torquedata/README.md) for more in depth information.

# TorqueDataConnect

TorqueDataConnect is the MediaWiki plugin that accesses the torquedata server.
It uses hooks to ask torquedata to render pages formatted for MediaWiki, and
provides JSON versions of the data through MediaWiki's api.

See the [TorqueDataConnect README](TorqueDataConnect/README.md) for more in depth information.
