# Inna Developer Guide

Inna is a minimal PHP MVC framework maintained by Payperlez. This guide describes Inna 2.0, which requires PHP 8.4 or newer. Read the upgrade guide when moving from an earlier release.

## Start here

1. [Build your first application](getting-started/quickstart.md).
2. [Configure your environment](getting-started/configuration.md).
3. Learn [routing](the-basics/routing.md), [controllers](the-basics/controllers.md) and [Twig views](the-basics/views.md).
4. Connect a [database](database/basics.md).
5. Add [encrypted uploads](storage/encrypted-uploads.md), [payments](services/payments.md) or [SMS](services/sms.md).

## Working safely

New services are optional and initialize when used. You can browse the homepage and guides without a database connection, a GeoIP file, Payperlez credentials or a storage key. Features that need missing configuration fail when invoked.

Use the [security guide](security/authentication.md) before exposing authentication endpoints. Payment acceptance, pending status and confirmed success are different states. Private storage encrypts files but does not decide who may read them.

## Reference and operations

See the [service reference](services/reference.md), [deployment guide](advanced/deployment.md), [testing guide](advanced/testing.md) and [upgrade guide](advanced/upgrading.md). Application-specific authorization, reconciliation jobs and data retention remain your responsibility.

