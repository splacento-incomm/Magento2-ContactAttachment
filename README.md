# Magento 2 Contact Form Attachment Module

This module extends the default Magento 2 contact form functionality by adding an attachment field, allowing users to upload files when submitting a contact request. This is particularly useful for businesses that require additional information or documentation from their customers.

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

## Configuration

1. **Allowed File Types and Maximum File Size:**
    - The configuration for allowed file types and maximum file size can be found in the Magento admin panel under:
      ```
      STORES > Configuration > General > Contact Attachment
      ```
    - Allowed file types should be specified as a comma-separated list (e.g., `.png, .jpg, .gif, .jpeg`).
    - Maximum file size should be specified in megabytes (MB).

2. **Create or Update Contact Us Email Template:**
    - You need to create a new contact us email template. This can be done in the Magento admin panel under:
      ```
      Marketing > Email Templates
      ```
    - After creating the email template, set it up as the email template for the contact form in:
      ```
      STORES > Configuration > General > Contacts > Email Options > Email Template
      ```

    - If you have your own or already defined email template, add the following code to include the attachment field in the email:
      ```html
      <tr>
          <td><strong>{{trans "Attachment"}}</strong></td>
          <td>{{var data.attachment}}</td>
      </tr>
      ```

## Usage

After following the installation and configuration steps, your Magento 2 contact form will include a file upload field, allowing users to attach files to their contact submissions.

## Credits

This module was inspired by a solution provided by [Akash Malik](https://magento.stackexchange.com/users/98321/akashmalik) on the [Magento Stack Exchange](https://magento.stackexchange.com/questions/304090/magento-2-3-3-add-file-upload-field-to-contact-page).

## License

This project is licensed under the MIT License.
