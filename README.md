[![pipeline status](https://chaos.expert/engelsystem/engelsystem/badges/main/pipeline.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![coverage report](https://chaos.expert/engelsystem/engelsystem/badges/main/coverage.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)](LICENSE)

# Engelsystem

Access it here: https://engelsystem.anthroarts.org!

Check out the [FC wiki](https://wiki.furcon.org/doc/engelsystem-Npw9BF62KE) for user instructions

This system was forked off of [engelsystem](https://github.com/engelsystem/engelsystem/). More official documentation can be found at: https://engelsystem.de/doc/.

## Local Installation

The Engelsystem can be started locally using the provided [startup.sh](startup.sh) program. Be sure to use [shutdown.sh](shutdown.sh) later to turn it off.

Take a look at [setup.md](setup.md) additionally to see how engelsystem is setup for FC.

## Local Deployment pushing to AWS

If developing locally against [fargate-infrastructure](https://github.com/anthroarts/fargate-infrastructure), you can upload changes to your AWS account by following these steps:

1. Authenticate with AWS
```
aws sso login --profile fargate
```

2. Build the docker image
```
docker build -f docker/Dockerfile . -t es_server
```

3. Authenticate docker with the AWS ECR instance
```
aws ecr get-login-password --region us-west-2 --profile fargate | docker login --username AWS --password-stdin <aws_account_id>.dkr.ecr.us-west-2.amazonaws.com
```

4. Tag the build with the repo, you can find this value inside the AWS account
```
docker tag es_server <aws_account_id>.dkr.ecr.us-west-2.amazonaws.com/engelsystem:<whatever_the_current_tag_is>
```

5. Upload the build
```
docker push <aws_account_id>.dkr.ecr.us-west-2.amazonaws.com/engelsystem
```

6. Restart the service
```
aws ecs update-service --force-new-deployment --service engelsystem --cluster Fargate --profile fargate --region us-west-2
```

## Github pushing to Prod AWS

As soon as you push (or merge to) main, this will automatically kick off a build and upload it to Prod AWS. After a few minutes the changes will be live on https://engelsystem.anthroarts.org!
