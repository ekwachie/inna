# Directory Structure

## Application files

| Path | Responsibility |
| --- | --- |
| index.php | Bootstrap and route definitions |
| config/config.php | Environment, headers and application construction |
| app/Controllers | HTTP actions |
| app/Models | Application persistence |
| app/Core | Router, request, response, database and base controllers |
| app/Core/Middlewares | Authentication, roles and CSRF checks |
| app/Services/Storage | Private encryption, upload policies and public images |
| app/Services/Payperlez | Payment/SMS API clients and webhook verification |
| app/Core/Utils | General helpers, including JWT, logging and mail |
| public/views | Twig templates |
| public/static | CSS and JavaScript |
| public/uploads | Optional intentionally public images |
| public/runtime | Generated Twig cache |
| docs | Markdown developer guides |
| migration | Migration classes |
| tests | Framework and HTTP regression tests |

Private encrypted storage is configured outside this tree. Its files must survive deployments.

## Namespaces

Composer maps app\\ to app/. Match folder capitalization on case-sensitive servers: app\\Core, app\\Controllers, app\\Models and app\\Services. Templates are resolved relative to public/views; render('orders/index') loads public/views/orders/index.twig.

## Legacy helpers

The old form field helper is incomplete; use explicit Twig forms as shown in the [forms guide](advanced/forms.md). Paystack and the old provider-specific SMS utility were removed in the 2.0 upgrade. See [upgrading](advanced/upgrading.md).

