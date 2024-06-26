# Magento 2.4.6 Contact Form Attachment Module

This module extends the default Magento 2.4.6 contact form functionality by adding an attachment field, allowing users to upload files when submitting a contact request. This is particularly useful for businesses that require additional information or documentation from their customers.

## Installation

1. Clone the repository to your Magento installation's `app/code/Debuglabs/ContactAttachment` directory:
    ```bash
    git clone https://github.com/splacento-incomm/Magento2-ContactAttachment.git app/code/Debuglabs/ContactAttachment
    ```

2. Enable the module by running the following commands:
    ```bash
    bin/magento module:enable Debuglabs_ContactAttachment
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento cache:clean
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

3. **Editing Custom Fields:**
    - If you need to add custom fields to the contact form, edit the form template located at:
      ```
      view/frontend/templates/form.phtml
      ```

## Usage

After following the installation and configuration steps, your Magento 2 contact form will include a file upload field, allowing users to attach files to their contact submissions.

## Credits

This module was inspired by a solution provided by [Akash Malik](https://magento.stackexchange.com/users/98321/akashmalik) on the [Magento Stack Exchange](https://magento.stackexchange.com/questions/304090/magento-2-3-3-add-file-upload-field-to-contact-page).

## License

This project is licensed under the MIT License.

