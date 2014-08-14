# cms-kit template "Table"

Installation path: backend/templates/table

## Description

**attention: alpha status**

This -partly[^1] generalized- Template shows your Entries as a searchable, sortable, paginated Table allowing a faster access than the default Template. Connected Entries are loaded as expandable Sub-Table (of course also sortable).

[^1]: this Template does not show generic Fields because of their nature (every Entry can have a different Structure witch dosen't fit into a "Table").

It is based on (and needs to run) the jQuery-Plugin jTable <http://www.jtable.org>

ToDo:

* add Filter for updateContent
* cut long Lines (Text-Areas) and replace other -non-textual content
* Documentation
* more inline-documentation
* fix som css-glitches
* make the detail-view not using the default-template

## Installation

### manual Installation

1. download and extract this Folder (grid) into backend/templates/
2. download jquerytable and unzip it into the sub-folder "jquerytable"

### Installation via package management

