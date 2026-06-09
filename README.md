<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/passbolt/passbolt_styleguide/blob/master/src/img/logo/logo_white.svg">
  <source media="(prefers-color-scheme: light)" srcset="https://github.com/passbolt/passbolt_styleguide/blob/master/src/img/logo/logo.svg">
  <img alt="passbolt-logo" src="https://github.com/passbolt/passbolt_styleguide/blob/master/src/img/logo/logo.svg">
</picture>
<br>
<br>

The open source password manager for teams.

[![License](https://img.shields.io/github/license/passbolt/passbolt)](LICENSE.txt)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Psalm level](https://img.shields.io/badge/Psalm-level%204-brightgreen.svg?style=flat)](https://psalm.dev/)


<details open="open">
<summary>Table of Contents</summary>

- [Introducing Passbolt](#introducing-passbolt)
- [Get Started](#get-started)
  - [Run it on your own server, natively](#run-it-on-your-own-server-natively)
- [Available Clients & Apps](#available-clients-and-apps)
  - [Browser Extensions](#browser-extensions)
  - [Mobile Apps](#mobile-apps)
  - [CLI](#cli)
  - [Desktop App](#desktop-app)
- [Contributing](#contributing)
- [Reporting a security issue](#reporting-a-security-issue)
- [License](#license)

</details>

---
<br>

![Passbolt on desktop, mobile, and cli](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-insitu.png)

# Introducing Passbolt

Passbolt is a security-first, open source password manager for teams. It helps organizations centralize, organize and share passwords and secrets securely.

What makes passbolt different?
- **Security:** Passbolt security model features user-owned secret keys and end-to-end encryption. It is audited multiple times annually, and [findings](https://help.passbolt.com/faq/security/code-review) are made public.
- **Collaboration:** Securely share and audit credentials, with powerful and dependable policies for power users.
- **Privacy:** Passbolt is headquartered in the EU :european_union: specifically in Luxembourg. Passbolt doesn't collect personal data or telemetry, and can be deployed in an air-gapped environment.

<br>

# Get Started

<a href="https://www.passbolt.com/ce/">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-CE-cta-light.png">
  <source media="(prefers-color-scheme: light)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-CE-cta-dark.png">
  <img alt="passbolt community edition CTA" src="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-CE-cta-dark.png">
</picture>
</a>
&nbsp; &nbsp;
<a href="https://www.passbolt.com/contact/pro/free-trial">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-pro-cta-light.png">
  <source media="(prefers-color-scheme: light)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-pro-cta-dark.png">
  <img alt="passbolt PRO edition CTA" src="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-pro-cta-dark.png">
</picture>
</a>
&nbsp; &nbsp;
<a href="https://www.passbolt.com/cloud/signup">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-cloud-cta-light.png">
  <source media="(prefers-color-scheme: light)" srcset="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-cloud-cta-dark.png">
  <img alt="passbolt Cloud edition CTA" src="https://github.com/passbolt/passbolt-links/blob/main/assets/readme/passbolt-cloud-cta-dark.png">
</picture>
</a>
<br>

### Run it on your own server, natively

|[![Install passbolt on Docker](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/docker-icon.svg)](https://www.passbolt.com/ce/docker) | [![Install passbolt on Kubernetes](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/kubernetes-icon.svg)](https://www.passbolt.com/ce/kubernetes) | [![Install passbolt on Ubuntu](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/ubuntu-icon.svg)](https://www.passbolt.com/ce/ubuntu) |[![Install passbolt on Debian](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/debian-icon.svg)](https://www.passbolt.com/ce/debian) | [![Install passbolt on RedHat](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/Redhat-icon.svg)](https://www.passbolt.com/ce/redhat) | [![Install passbolt on Raspberry Pi](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/raspberry-pi-icon.svg)](https://www.passbolt.com/ce/raspberry)  | [![Install passbolt on RockyLinux](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/rockylinux-icon.svg)](https://www.passbolt.com/ce/rockylinux) |
|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| [![Install passbolt on AlmaLinux](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/almalinux-icon.svg)](https://www.passbolt.com/ce/almalinux) | [![Install passbolt on Oracle](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/oracle-icon.svg)](https://www.passbolt.com/ce/oracle)  | [![Install passbolt on Fedora](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/fedora-icon.svg)](https://www.passbolt.com/ce/fedora) | [![Install passbolt on openSuse](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/openSUSE-icon.svg)](https://www.passbolt.com/ce/opensuse)  | [![Install passbolt on AWS](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/AWS-icon.svg)](https://www.passbolt.com/ce/aws) |  [![Install passbolt on DigitalOcean](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/digitalocean-icon.svg)](https://www.passbolt.com/ce/digitalocean) | [![Install passbolt on CentOS](https://github.com/passbolt/passbolt-links/blob/main/assets/readme/centos-icon.svg)](https://www.passbolt.com/ce/centos) |

<br>

## Available Clients And Apps

### Browser Extensions

- [Chrome](https://chrome.google.com/webstore/detail/passbolt-open-source-pass/didegimhafipceonhjepacocaffmoppf) - Brave, Opera, Vivaldi, & other Chromium browsers
- [Firefox](https://addons.mozilla.org/en-US/firefox/addon/passbolt/)
- [Edge](https://microsoftedge.microsoft.com/addons/detail/passbolt-open-source-pa/ljeppgjhohmhpbdhjjjbiflabdgfkhpo)

### Mobile Apps

- [App Store](https://apps.apple.com/nz/app/passbolt-password-manager/id1569629432)
- [Google Play Store](https://play.google.com/store/apps/details?id=com.passbolt.mobile.android)

### CLI

Install passbolt CLI tool: [go-passbolt-CLI](https://github.com/passbolt/go-passbolt-cli)

### Desktop App
Coming soon [see the pre-alpha version here](https://github.com/passbolt/passbolt-windows).

<br>

# Contributing

Please check [CONTRIBUTING.md](CONTRIBUTING.md) for more information about how to get involved.

# Reporting a security Issue

If you've found a security related issue in Passbolt, please don't open an issue on GitHub. Follow our responsible disclosure process: https://www.passbolt.com/docs/contribute/security/vulnerability/.

# License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License (AGPL) as published by the Free Software Foundation version 3.

The name "Passbolt" is a registered trademark of Passbolt SA, and Passbolt SA hereby declines to grant a trademark license to "Passbolt" pursuant to the GNU Affero General Public License version 3 Section 7(e), without a separate agreement with Passbolt SA.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).
