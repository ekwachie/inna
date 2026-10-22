# GitHub deployment to CWP

`.github/workflows/deploy-production.yml` verifies every push to `main`, then
deploys it to `https://inna.payperlez.org` over SSH. The production job
fast-forwards an existing server checkout, installs locked production
dependencies, validates the production environment, and checks the live site.

## One-time server setup

Create the `inna.payperlez.org` domain and TLS certificate in CWP. Its document
root must be the INNA repository checkout because the application entry point is
the root `index.php`. Clone `git@github.com:ekwachie/inna.git` as `payperle`,
checkout `main`, run the production Composer install, and create a private `.env`
from `env.example` with at least:

```dotenv
APP_ENV=production
DOMAIN=inna.payperlez.org
```

The server checkout needs noninteractive read access to GitHub. Preserve `.env`,
private storage, encryption keys, database data, and logs across deployments.
Database migrations are intentionally not run by the workflow.

## GitHub production secrets

Add these secrets to the repository's `production` environment:

- `VPS_HOST`: `138.201.95.132`
- `VPS_USER`: `payperle`
- `VPS_PORT`: the SSH port, normally `22`
- `DEPLOY_PATH`: the absolute server checkout path under `/home/payperle`
- `VPS_SSH_KEY`: the deployment private key authorized for `payperle`
- `VPS_KNOWN_HOSTS`: the trusted VPS host key under the fixed alias
  `payperlez-production`

Generate the known-hosts value directly on the VPS as root:

```sh
awk '{print "payperlez-production " $1 " " $2}' /etc/ssh/ssh_host_ed25519_key.pub
```

Do not disable SSH host verification. The key used by GitHub Actions should be
separate from the read-only deploy key that the server uses to fetch this
repository.

## DNS

Point the `inna` DNS record for `payperlez.org` to `138.201.95.132`. Wait for
public DNS resolution and issue the TLS certificate before the first deployment;
the workflow treats a failed HTTPS request as a failed deployment.
