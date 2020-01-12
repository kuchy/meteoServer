#!/bin/bash

scriptDir=$(dirname -- "$(readlink -f -- "$BASH_SOURCE")")

$scriptDir/stop.sh
$scriptDir/startup.sh
