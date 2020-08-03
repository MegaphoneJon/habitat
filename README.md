# habitat

This module customizes settings for non-production environments.

Presently, it disables the following checks:
* cron 
* debug enabled (Development sites only)
* Stripe webhooks
* reduces the severity of the "non-production environment" check to "Info".

## Requirements

* PHP v7.0+
* CiviCRM 5.0+

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl habitat@https://github.com/MegaphoneJon/habitat/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/habitat.git
cv en habitat
```

## Usage

Install and forget about it.
