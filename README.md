# tweet2json - No API required! - Deprecated

### Example Usage
```php
<?php

include "tweet2json.php";

echo "<pre>";
echo json_encode((new Tweet2JSON)->getJSON("TwitterDev"), JSON_PRETTY_PRINT);
echo "</pre>";

?>
```
### Reference Links (Credits) :
[Ref 1. Stackoverflow](https://stackoverflow.com/questions/71438223/twitter-embed-timeline-no-longer-works-as-of-today)
<br>
[Ref 2. Stackoverflow](https://stackoverflow.com/questions/53778331/is-there-a-way-to-get-public-tweets-of-a-user-without-using-the-twitter-develope)
