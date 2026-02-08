# php-gcode-thumbnail

A very basic PHP snippet to extract a thumbnail embedded by slicer from a GCODE file using PHP 

Extracts image without additional libraries and sends it together with some additional headers

```
 # curl -v http://api.local.lan/temp/gcode-thumbnail.php
*   Trying 192.168.129.12:80...
* Connected to api.local.lan (192.168.129.12) port 80 (#0)
> GET /temp/gcode-thumbnail.php HTTP/1.1
> Host: api.local.lan
> User-Agent: curl/7.81.0
> Accept: */*
>
* Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Server: nginx/1.18.0
< Date: Sun, 08 Feb 2026 11:54:47 GMT
< Content-Type: image/png
< Content-Length: 14890
< Connection: keep-alive
< Content-Disposition: inline; filename='test.gcode.thumbnail'
< X-Gcode-FileName: test.gcode
< X-Gcode-FileMime: text/plain
< X-Gcode-FileSize: 21199
< X-Gcode-Timestamp: 1770544252
< X-Gcode-SpecifiedStreamLength: 19856< X-Thumb-ProcessedStreamLength: 19856
< X-Thumb-StreamLengthMatch: Yes
< X-Thumb-Resolution-X: 144
< X-Thumb-Resolution-Y: 144
< X-Thumb-MimeType: image/png
< X-Thumb-FileSize: 14890
< X-Thumb-Timestamp: 1770551687
< [...]
``

Feel free to use, public domain.
