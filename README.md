Rawr
=== 
 
**Rawr** is a PHP wrapper for the `exiv` and `exiftool` command-line utilities. It enables Preview Extraction, EXIF Data Examination, and EXIF Data Transfer to other image files.

In short: **Rawr** makes it easier to work with Canon CR2 (RAW) images from within PHP.

Installation Requirements
---

* PHP 5.4+ or PHP 7
* A writeable temporary folder for performing operations
* System Binaries
  * **`exiv2`** to list and extract previews.
  * **`exiftool`** to transfer EXIF data between files.
  
Running the unit test suite requires both `exiv2` and `exiftool` present in your path. Testing EXIF data transfer requires the `php-exif` extension; these tests will be skipped if the extension is not found.

Adding Rawr To Your Project
---

Require **Rawr** with Composer:

    composer require beryllium/rawr
    
Then, in your code, instantiate **Rawr**:

    $rawr = new Beryllium\Rawr\Rawr('path/to/sandbox', 'path/to/exiv2', 'path/to/exiftool');

(You can leave off the exiftool value if you are not interested in transferring Exif data.)

Why Do I Need Rawr?
---

### Faster Thumbnails for RAW Photos

Generating thumbnails from RAW photos in PHP is *very* slow. It's also clunky to wire up Imagick to get the proper output.

Each RAW photo actually has one or more built-in JPG previews stored alongside the camera's raw sensor data. Extracting this preview is a handy shortcut for avoiding PHP's slowness. Batch thumbnail operations are much faster with this approach.

**Rawr** can list previews:

~~~
$rawr->listPreviews('path/to/IMAGE.CR2')
~~~

The preview list is an array containing information about each preview. Typically, there is a full-size preview in addition to one or more smaller thumbnails.

The example output below demonstrates:

-  **1**: 160x120 JPG
-  **2**: 668x432 TIFF
-  **3**: 5184x3456 JPG (the full-sized preview) 

~~~
array(
    array(
        'index'  => 1,
        'type'   => 'image/jpeg',
        'height' => 120,
        'width'  => 160,
        'size'   => 14416,
    ),
    array(
        'index'  => 2,
        'type'   => 'image/tiff',
        'height' => 432,
        'width'  => 668,
        'size'   => 1731456,
    ),
    array(
        'index'  => 3,
        'type'   => 'image/jpeg',
        'height' => 3456,
        'width'  => 5184,
        'size'   => 1869241,
    ),
);
~~~

**Rawr** can extract individual previews:

~~~
// extracts the specified preview to the sandbox location and returns the resulting temporary filename 
$previewFile = $rawr->extractPreview('path/to/IMAGE.CR2', 3)
~~~

You'll want to move the extracted `$previewFile` out of the sandbox if you want to preserve it. If you're just using it to generate thumbnails, you can leave it in the sandbox and locate the thumbnails elsewhere (and then unlink the `$previewFile` when you're done).

Don't forget to transfer Exif data to generated thumbnails!

### Preserving EXIF Data

Every image taken with your digital camera has special data embedded in the file. This data records the time, camera settings, and even portrait/landscape settings for that image. With some newer cameras, the data can also include GPS coordinates.

Extracting the preview, or even generating a thumbnail from the extracted preview, can result in the loss of this data. Imagick and PHP do not seem to preserve it properly.

**Rawr** can transfer the EXIF data from the original CR2 file to a JPG:

~~~
// to any jpg file
$rawr->transferExifData('path/to/IMAGE.CR2', 'path/to/new_thumbnail.jpg');

// to the preview image you extracted
$rawr->transferExifData('path/to/IMAGE.CR2', $previewFile);
~~~

Keep in mind that transferring Exif data can be slow. Expect it to take one or two seconds per call, depending on server CPU/RAM/Disk speed.

**Rawr** can extract the EXIF data into a consumable format, allowing you to make decisions based on the data:

~~~
// translated format
$translatedData = $rawr->listExifData('path/to/IMAGE.CR2', Rawr::EXIF_TRANSLATED);

// raw format
$data = $rawr->listExifData('path/to/IMAGE.CR2');
~~~

The Past and Future of Rawr
---

I built **Rawr** as part of a home photography project. I needed to quickly generate thumbnails for 22,000 CR2 files. Doing it by rendering the RAW out to a JPG using ImageMagick could've taken years.
 
If the project helps you out, great! If you find issues with it, please contribute by either logging an issue or a PR on the project.

In the future, I would like to support a wider variety of RAW formats.