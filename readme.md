# cms-kit template "Table"

Installation path: backend/templates/table

## Description

**attention: alpha status**

This -partly generalized- template shows entries as a searchable, sortable, paginated table allowing a faster access than the default template.
Connected entries are loaded as expandable sub-table (of course also sortable).

This template does not show generic fields because of their nature
(every entry can have a different structure witch dosen't fit into a "table").

It is based on (and needs to run) the jQuery-Plugin jTable <http://www.jtable.org>

ToDo:

* add filter for updateContent
* cut long lines (text-areas) and replace other -non-textual content
* Documentation
* more inline-documentation
* fix som css-glitches
* make the detail-view not using the default-template

## Installation

### manual Installation

1. download and extract this Folder (grid) into backend/templates/
2. download jquerytable and unzip it into the sub-folder "jquerytable"

### Installation via package management

