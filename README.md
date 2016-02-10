Rawr
===

Rawr is a library for working with Canon CR2 images from within PHP. It wraps `exiv` and `exiftool` to enable Preview Extraction, Exif Data Examination, and Exif Data Transfer to other image files.

Why Do I Need Rawr?
---

It turns out that PHP is pretty unfriendly for generating thumbnails from RAW photos. Even if you can get it to work, it still takes an unreasonable (albeit probably unavoidable) amount of time for larger images. A handy shortcut is being able to extract the camera's own embedded JPEG preview from the RAW photo. Batch thumbnail generation will be much faster with this approach.

Also, the resulting extracted preview will lack the Exif data of the original RAW photo. This also seems to happen when generating thumbnails based off of the extracted preview.

Rawr gives you the ability to list and extract the previews, as well as examine and transfer the Exif data from the original RAW photo to the extracted preview (as well as any derived thumbnails).

What Are Rawr's Requirements?
---

Rawr requires access to the `exiv2` tool in order to list and extract previews.

Rawr also requires read/write access to a sandbox folder to perform operations in.

Optionally, Rawr can use the `exiftool` utility to transfer Exif data between files.

Rawr is written for PHP 5.4 and above.

Running the unit test suite requires the php-exif extension, and for `exiv2` and `exiftool` to be present in your path.

Adding Rawr To Your Project
---

Require Rawr with Composer:

    composer require beryllium/rawr
    
Then, in your code, instantiate Rawr:

    $rawr = new Beryllium\Rawr\Rawr('path/to/sandbox', 'path/to/exiv2', 'path/to/exiftool');

(You can leave off the exiftool value if you are not interested in transferring Exif data.)

Using Rawr
---

To list all previews embedded in a CR2 file:

    print_r($rawr->listPreviews('path/to/IMAGE.CR2'));
    
To extract preview #3:

    $previewFile = $rawr->extractPreview('path/to/IMAGE.CR2', 3);
    
To list Exif data:

    // raw format
    print_r($rawr->listExifData('path/to/IMAGE.CR2'));
    
    // translated format
    print_r($rawr->listExifData('path/to/IMAGE.CR2', Rawr::EXIF_TRANSLATED));
    
To transfer Exif data to another image:

    // to any jpg
    $rawr->transferExifData('path/to/IMAGE.CR2', 'path/to/new_thumbnail.jpg');
    
    // to the preview image you extracted
    $rawr->transferExifData('path/to/IMAGE.CR2', $preview);
    
Keep in mind that transferring Exif data can be slow. Expect it to take one or two seconds per call.

You'll want to move the extracted preview out of the sandbox if you want to preserve it. If you're just using it to generate thumbnails, you can leave it in the sandbox and locate the thumbnails elsewhere (and then unlink the preview in the sandbox when you're done).

Don't forget to transfer Exif data to generated thumbnails!

The Past and Future of Rawr
---

I built Rawr as part of a home photography project. I needed to quickly generate thumbnails for 22,000 CR2 files. Doing it by rendering the RAW out to a JPG using ImageMagick could've taken years.
 
If the project helps you out, great! If you find issues with it, please contribute by either logging an issue or a PR on the project.

In the future, I would like to give it support for the Nikon RAW format (NEF), but I have not yet attempted that.