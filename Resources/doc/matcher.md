# CjwMultiSiteBundle SiteAccess Matchers



## Map/Host Matcher
 
### Features

As the original `Map/Host` matcher, siteaccesses are matched based on the host name, but instead of matching the whole host name, it is checked wether the *beginning* of the host name matches. 

### Usage

```yaml
match:
    \Cjw\MultiSiteBundle\Matcher\MapHost:
        www.example.com: example_user
        admin.example.com: example_admin
```
In this example, these hostname would all resolve to the `example_user` siteaccess.

* www.example.com
* www.example.com.dev.local
* www.example.com.stage.local


## Map/HostURI Matcher

### Features

As the `Map/Host` matcher above, this matches on the _beginning_ of the host name and additionally the _URI_ part.

In addition, it features the concept of a _default siteaccess_. Why this, when we already have a `default_siteaccess` key in the siteaccess configuration? The reason simple: unlike the standard behaviour, this matcher always appends the URI part, thus eliminating duplication of caches and content.

### Usage

```yaml
match:
    \Cjw\MultiSiteBundle\Matcher\MapHostURI:
        www.example.com/de/(default): example_user_de
        www.example.com/en: example_user_en
        www.example.net/de: example_net_user_de
        www.example.net/en: example_net_user_en
```
In this example, these hostname result in the following siteaccesses.

* www.example.com   -> example_user_de (*)
* www.example.com/de -> example_user_de
* www.example.com/en -> example_user_en
* www.example.net/de -> example_net_user_de
* www.example.net/en -> example_net_user_en

(*) generated routes would be rewritten to include the `/de` URI part

### Map/HostURILanguage Matcher

### Features

As the `Map/HostURI` matcher above, this matches on the _beginning_ of the host name and additionally the _URI_ part.

When no URI part is given, the browser language is used to match instead of the URI part. If no match is found, then the default logic from above is applied.

### Usage

Identical to `Map/HostURI` above.