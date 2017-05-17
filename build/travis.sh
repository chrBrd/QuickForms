#!/usr/bin/env bash

## Test variables.
PHPMD_CHECKS="cleancode,codesize,controversial,design,naming,unusedcode"
FAILED_TESTS=()

## Bash variables.
RED="\033[1;31m"
GREEN="\033[1;32m"
YELLOW="\033[1;33m"
BLUE="\033[1;34m"
RED_BG="\033[1;37;41m"
GREEN_BG="\033[0;30;42m"
YELLOW_BG="\033[0;30;43m"
NO_COLOR="\033[0m"

## Functions.

# Print multiple line breaks.
function lineGap {
    printf "\n\n"
}

# Print a section header.
function sectionHeader {
    lineGap
    printf "${YELLOW}***\n"
    printf "${YELLOW}$1...\n"
    printf "${YELLOW}***"
}

# Run a test
#   $1  The command to run.
#   $2  The test's name.
function runTest {
    lineGap
    printf "${BLUE}$2\n"
    $1
    RESULT="$?"
    if [ "${RESULT}" -eq "0" ]; then
        printf "${GREEN}$2 passed!\n"
    else
        printf "${RED}$2 failed!\n"
    fi

    return "${RESULT}"
}

# Run an essential test.
#   $1  The command to run.
#   $2  The test's name.
function essentialTest {
    runTest "$1" "$2"
    if [[ "$?" -ne "0" ]]; then
        FAILED_TESTS+="$2\n"
        export FAILED=1
    fi
}

# Run all the essential tests.
function requiredTests {
    sectionHeader "Beginning required tests"
    essentialTest "${TRAVIS_BUILD_DIR}/vendor/bin/phpunit -v --coverage-clover build/logs/clover.xml" "Unit & Integration Tests"
    essentialTest "${TRAVIS_BUILD_DIR}/vendor/bin/phpcs -p --colors --report=full --ignore=vendor --standard=PSR2 ." "Code style checks"
    essentialTest "${TRAVIS_BUILD_DIR}/vendor/bin/phpmd ${TRAVIS_BUILD_DIR} text ${PHPMD_CHECKS} --exclude vendor,Tests" "PHP Mess Detector checks"
    lineGap

    printf "${YELLOW_BG}***Essential tests complete.***${NO_COLOR}\n"

    if [[ "${FAILED}" ]]; then
        printf "${RED_BG}The following essential tests failed:${NO_COLOR}\n"
        printf "${RED}${FAILED_TESTS}"
    else
       printf "${GREEN_BG}All essential tests passed.${NO_COLOR}\n"
    fi
}

# Run all the optional tests.
function optionalTests {
    sectionHeader "Beginning optional tests"
    runTest "${TRAVIS_BUILD_DIR}/vendor/bin/phpcpd --exclude Tests --exclude vendor ." "Code duplication checks"
    runTest "${TRAVIS_BUILD_DIR}/vendor/bin/phpmd ${TRAVIS_BUILD_DIR}/Tests text ${PHPMD_CHECKS}" "PHP Mess Detector Test directory checks"
    lineGap

    printf "${YELLOW_BG}***Optional tests complete.***${NO_COLOR}\n"
}

# Exit the test script.
function exitScript {
    lineGap
    if [[ "${FAILED}" -ne 0 ]]; then
        printf "${RED_BG}Test script has failed!${NO_COLOR}\n"
        exit 1
    else
        printf "${GREEN_BG}Test script has finished successfully.${NO_COLOR}\n"
        exit 0
    fi
}

## Sequence the tests.
requiredTests
optionalTests
exitScript
