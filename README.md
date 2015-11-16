# SampleTableUpdateFunctions
Incentient's Smartcellar product has an Admin application that is used by clients and Project Managers to customize their product (custom CMS).  When a new client application is built, a database developer/engineer builds the initial database.  The initial database build consists of downloaded custom beverage templates, and enabling the features that this client will be using.  Much of this process should be done by Project Managers, but there was never a sufficient interface for them to use.  So if a client wants to turn on 'Wine Pairings to Desserts', a software/database engineer would have to add a row to the pairings table to enable this.  If this was an initial build, not such a big deal.  If the client was adding this on, the sql query is put in a Jira ticket, QA would test and then run the query by hand on the client site.  There are many features that are enabled this way, which need to be automated so that development is not needed to turn on features.  So I started working on Project Mangement features that would allow project managers to customize client applications without the need of a developer.  
So I started working on a basic table edit functions/classes that can be used to build the PM build this functionality into the Admin.
The first tables/features that have been implemented are enabling/disabling categories, adding client labels (used for translations), and maintaining languages_supported table (used for translations).
 
The files included are:

UpdateTableRequestProcessor.php - handles request from front end.
TableAdminGateway.php -  Base class that handles all logic for updating tables.
BevCategories.php  - class for bev_categories table
ClientLabels.php   - class for client_labels table
LanguagesSupported.php  - class for languages_supported table

displayTable.tpl, editTable,tpl - smarty template files.
editTable.js - javascript code.


