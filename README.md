# web-content-extractor

This is a server application for custom web content extraction.

### Setup:
1. Import `db_schema.sql` to a database
2. Fill in database connection fields in `config.php`
3. Setup additional sources in `extractor.php`
4. Configure `Access-Control-Allow-Origin` in `api/poi/read.php`, to restrict access to the api. 
5. Optionally, setup a cron job to periodically execute the application for fresh results.

#### Live example:
[News Rush](https://newsrush.gr)

#### Special Thanks to the `simplehtmldom` team:
- Website: http://sourceforge.net/projects/simplehtmldom/
- Additional projects: http://sourceforge.net/projects/debugobject/
- Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
- Contributions by:
  - Yousuke Kumakura (Attribute filters)
  - Vadim Voituk (Negative indexes supports of "find" method)
  - Antcs (Constructor with automatically load contents either text or file/url)
