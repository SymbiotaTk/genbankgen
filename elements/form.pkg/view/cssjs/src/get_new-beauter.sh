#!/bin/sh

function g() {
    wget "$1";
}

g https://cdn.jsdelivr.net/gh/outboxcraft/beauter/beauter.min.css
g https://cdn.jsdelivr.net/gh/outboxcraft/beauter/beauter.min.js
