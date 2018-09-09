#!/bin/bash

##
# Publish the Library to Packagist.org
###########################################################

GIT_ROOT=$(git rev-parse --show-toplevel)
VERSION=$1

##
# Functions
##
update_version () {
    TARGET="composer.json"
    cd ${GIT_ROOT}
    if [[ -n ${VERSION} && -e ${TARGET} ]]; then
        echo "Updating version in ${TARGET} to ${VERSION}"
        sed -E -i.bak "s|\"version\": \"[0-9]+\.[0-9]+\.[0-9]+\"|\"version\": \"${VERSION}\"|" ${TARGET}
    fi
}

git_push() {
    echo "Adding files to git, committing changes and pushing to remote..."
    cd ${GIT_ROOT}
    git add .
    git commit -m "version ${VERSION}"
    git push
}

git_add_tag() {
    echo "Adding a git tag for the new version and pushing to remote..."
    cd ${GIT_ROOT}
    git tag -a "${VERSION}" -m "${VERSION}"
    git push --prune --tags
}

##
# Run
##
update_version
git_push
git_add_tag
