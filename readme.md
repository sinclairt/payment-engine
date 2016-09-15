#Payment Engine

###Installation
``` composer require sinclair/payment-engine ```

Register the service provider in the config/app.php array:
``` \Sinclair\MultiTenancy\Providers\MultiTenancySericeProvider::class ```

#####Optional
Publish the config:
``` php artisan vendor:publish ```

#####Config

**ignore-roles** _default([ 'super-admin' ])_    
    if you don't want your app to apply the multi-tenancy constraints add the roles here, I've assumed super-admin out of the box, but feel free to change this or leave as an empty array if you're not using roles
    
**relationship name** _default('tenant')_   
    this is the name of the relationship method on your models, the plural is assumed for many-to-many
    
**relationship table** _default('tenants')_    
    name of the tenant table
    
**relationship class** _default(\App\Models\Tenant::class)_    
    class name of the tenant model
    
**relationship polymorph-term** _default('tenantable')_    
    the polymorphic term used in you models relationships
    
**relationship foreign key** _default('tenant_id')_    
    the foreign key used on your models tables
    
**relationship slug column name** _default('slug')_    
    the column where the slug is stored on the tenant table
    
**should apply callback** _default(null)_   
    if you want to use custom logic to decide whether to apply the scopes set you callback here the user object and ignored roles array are passed into this
 
**should apply default** _default(true)_    
    if true will automatically apply the scopes to the models, this is only used if the callback is null and the ignore roles are empty
    
**role class** _default(\App\Models\Role::class)_    
    The name of your role class - leave as an empty string if you're not using roles 

###Usage
The multi-tenancy package works by using global scopes to restrict queries based on a 
constant ``` TENANT_SLUG ```. This package assumes you are using a sub-domain to control 
which tenant is required by your user; there is a helper function to place inside 
bootstrap/app.php ``` bootstrapMultiTenancy() ```, but, of course, you are free to set the 
constant however you wish, but in order for this package to work it must be set.

To avoid having a foreign key on every single database table, the multi-tenancy package uses 
a models relationships to constrain queries. There a three ways a model can be connected to a 
tenant:
 * Directly - the model belongs to the tenant
 * Through - the model belongs to the tenant through another model or chain of models
 * Morph - the model belongs to the tenant via a many-to-many relationship (poly-morphic included)
 
There are three scopes that can be applied based on the criteria above, and there are traits to ease the implementation for two of them:
the ``` BelongsToTenant``` and ``` MorphToTenant ``` scopes have respective traits, just use them in your models and that's it.

The other scope ``` BelongsToTenantThrough ``` requires a little more set up, but only a little, all you need to do is set the relationship(s) to go through to get to the tenant when applying the scope in the models boot method, for example:
``` 
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BelongsToTenantThrough('user'));
    }
```

The vehicle model in the example above belongs to the Tenant in one way or another and so we will go through this relationship to get to the tenant.

You can also use dot notation to chain relationships i.e. `driver.user`, this applies when there are series of relationships to go through to get to the tenant.

One final scenario is if your model is a polymorphic many-to-many and could potentially belong to the tenant through various channels, easy, pass in an array of all the potential links to the tenant, the multi-tenancy package only needs to find one:

``` static::addGlobalScope(new BelongsToTenantThrough([ 'users', 'drivers.user', 'locations' ])); ```

In this example we have a Phone model which stores numbers against various models: users, drivers, and locations, so we need to check whether it belongs to our given tenant through any of those connections.

###Auth

This package provides a Tenant Auth Guard to use out of the box, be aware of this when you are interacting with you app, as the web guard is the default; it may be worth setting something like this to handle it:
```
// config/auth.php

'guard'     => !is_null(constant('TENANT_SLUG')) ? 'tenant' : 'web',
```

###User Model
Because the multi-tenancy package allows you to have a single database, it means a user can belong to more than one tenant if you want them to, useful for admin roles (although I recommend using the ignore-roles config value). If you use the sub-domain solution for multi-tenancy this will force the user to login to new tenant areas but it means they can have the same credentials, roles, and permissions across tenants. 

Your User model needs to use the ``` IsMultiTenantUser ``` trait if you are using roles, it provides a piece of logic for the scopes, but it also uses the ``` MorphToTenant ``` trait and sets the roles relationship for you. If you're not using roles, be sure to use the ``` MorphToTenant ``` trait in your User model.