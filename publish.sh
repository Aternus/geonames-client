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
        sed -E -i.bak "s|\"version\": \"[1-9]+\.[0-9]+\.[0-9]+\"|\"version\": \"${VERSION}\"|" ${TARGET}
    fi
}

git_commit() {
    echo "Adding files to git and committing changes..."
    cd ${GIT_ROOT}
    git add .
    git commit -m "version ${VERSION}"
}

git_add_tag() {
    echo "Adding a git tag for the new version..."
    cd ${GIT_ROOT}
    git tag -a "${VERSION}" -m "${VERSION}"
}

git_push() {
    echo "Pushing changes to the remote repo..."
    cd ${GIT_ROOT}
    git push
}

##
# Run
##
update_version
git_commit
git_add_tag
git_push
