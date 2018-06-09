#!/bin/sh

function g() {
    wget "$1";
}

g https://rawgit.com/outboxcraft/beauter/master/beauter.min.css
g https://rawgit.com/outboxcraft/beauter/master/beauter.min.js
