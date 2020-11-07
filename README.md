# GoogleFontDownload
Download Google fonts within Gamemaker projects

HOW TO USE:
First, go to https://developers.google.com/fonts/docs/developer_api
and get a key to use. Paste it as a string in the google_api_key variable

There are some variables that you can customize to your preference.

All function are located in the 
"google_font_downloader" object. And must be called by referencing
"google_font_downloader.<function>" Feel free to rename the object if you want to
...the object's name won't have any impact
The object handles all ASYNC events
It's marked as persistent because it takes a while to retrieve the list of fonts
BEFORE DOWNLOADING ANY FONTS, be sure to use google_font_request_list()
And then wait until google_font_ready() is true before downloading

To download a font:
Call google_font_download(font, size, italic, save, [weight])
Font - The name of the font to download (Case sensitive, include spaces)
Size - How big Gamemaker should make the font (Same value you'd use when making a font within gamemaker)
Italic - Whether or not the font should be italic.
		Not all Google Fonts can be italic, so if there is not an italic variant, the regular font will be returned
Save - Whether or not to save the font to the disc or to immediately delete it
Weight - Optional, The boldness of the font. As with Italic, not all fonts have weights
		So if you include a weight that the font does not support, then the closest weight that is supported will be chosen
		(Weight values that might be supported by some fonts:
		100-thin, 200-extra light, 300-light 400-regular, 500-medium,
		600-semi bold, 700-bold, 800-extra bold, 900-black)
		(tip: use 100 to always get the thinnest available font variant
		and 900 to get the boldest available font variant)
The downloader will always try to get the closest possible font variant,
but it might not be available

google_font_download() will return a unique FontRequest
Use that FontRequest to check and see when the font has finished downloading
font_request.is_available //Whether or not the font has downloaded 
font_request.font_resource //The created Gamemaker font to use with draw_set_font() (stays undefined until the font has downloaded)
font_request.download_size //How big the font's download size is
font_request.download_progress //How much data has been downloaded for the font
font_request.request_timeout //Whether or not the download for the font has timed out (as set by max_wait_time)

Once is_available is true or font_resource is no longer undefined, you can use the font
with draw_set_font()

There are functions to clear the font download folder
