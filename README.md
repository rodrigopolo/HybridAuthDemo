# HybridAuthDemo
A small HybridAuth portable demo based on [this other demo](http://www.sitepoint.com/social-logins-php-hybridauth/) wich is also in [github](https://github.com/sitepoint-editors/HybridAuth-Demo-App).


### Installation instruction

1. Register a Twitter and a Facebook app.
2. Create MySQL table.
3. In the `app/` folder, copy `config-empty.php` to `config.php` and fill your settings.

##### User table schema

```
CREATE TABLE `users` (
  `id`          int(11)      unsigned NOT NULL AUTO_INCREMENT,
  `snid`        smallint(2)  unsigned DEFAULT NULL,
  `identifier`  bigint(20)   unsigned DEFAULT NULL,
  `email`       varchar(191)          DEFAULT NULL,
  `first_name`  varchar(191)          DEFAULT NULL,
  `last_name`   varchar(191)          DEFAULT NULL,
  `avatar_url`  varchar(191)          DEFAULT NULL,
  `reg`         datetime              DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `snid` (`snid`),
  KEY `identifier` (`identifier`)
) ENGINE=InnoDB;
```

-------

## License

(The MIT License)

Copyright (c) by Rodrigo Polo http://RodrigoPolo.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.


