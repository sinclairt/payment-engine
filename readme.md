#Payment Engine

###Installation
``` composer require sinclair/payment-engine ```

Register the service provider `Sinclair\PaymentEngine\PaymentEngineServiceProvider`.

Run `php artisan vendor:publish` to publish the migrations, config files, and routes.

###Usage

#####Commands

There two commands to generate and process transactions and plans to help you out.

The generate transaction command takes a plan and generates a transaction for you. You can tell it whether you would like it to `--calculate=true` the charges and whether to `--process=true` the transaction when its generated.

The process command takes a transaction or, if omitted, all failed transactions and process them through thr gateway for you.

######Api

There is a resourceful controller for each of Plan, Charge, Transaction, and Item. The published routes file lists all the endpoints but obviously you can change which end points you want to expose.

#####Events

There are five events that are fired which you can listen for:
* TransactionFailedToGenerate
* TransactionFailedToProcess
* TransactionGenerated
* TransactionProcessed
* TransactionWasRedirected

######Gateways

This package includes a few Omnipay gateways out the box but you can add more if you wish, just make sure to list them in the supported_gateways inside the config file.

This package does not support redirected responses, handle them how you wish, but the intent for this package is lights out transactions.

######Scheduling

The Plan and Charge models use Schedulable to schedule their occurrence be sure to check out the documentation there for more information.  

######Repositories

There is a repository for each Plan, Charge, Transaction, and Item.

######Config

The config value for both gateway and settings, can handle a callback to create a more flexible solution.