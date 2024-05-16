# Magento 2 Contact Form Attachment Module

This Magento 2 module adds a file upload field to the contact form, enabling users to attach files when submitting the contact form. This solution is compatible with Magento 2.4.6.

## Installation

1. Clone or download this repository and place it in the `app/code/Debuglabs/ContactAttachment` directory of your Magento installation.

2. Enable the module by running the following commands:
    ```bash
    bin/magento module:enable Debuglabs_ContactAttachment
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento cache:clean
    ```

3. Copy the contact form template to your theme's directory:
    ```bash
    cp vendor/magento/module-contact/view/frontend/templates/form.phtml app/design/frontend/<VENDOR_NAME>/<YOUR_THEME>/Magento_Contact/templates/form.phtml
    ```

4. Open the copied `form.phtml` file and add the `enctype="multipart/form-data"` attribute to the form tag:
    ```html
    <form action="<?php /* form action here */ ?>" method="post" enctype="multipart/form-data">
    ```

## Usage

After following the installation steps, your Magento 2 contact form will include a file upload field.

## Credits

This module was inspired by a solution provided by [Akash Malik](https://magento.stackexchange.com/users/98321/akashmalik) on the [Magento Stack Exchange](https://magento.stackexchange.com/questions/304090/magento-2-3-3-add-file-upload-field-to-contact-page).

## License

This project is licensed under the MIT License.
