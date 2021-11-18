# Save form in Database
This is an extension of the backend of TYPO3.

Save form in Database, all elements  in The submitted form will save in databases.

## Installation

- Clone the Repository «OR» `composer require  "othman/saveformtodb"`
- Activate extension in TYPO3 Extension Manager
- Add Finisher "Save the mail to Database" to form. (screenshot- 1)

## Extension Settings

the default option can change in Extension Configuration

Default

- Who can delete an Email (Admin or all Be-Users)
    - Default: true
    - `permission.onlyAdminCanDelete` `(boolean)`
- Pagination items per page
    - Default: 25
    - `pagination.itemsPerPage` `(integr)`
- Csv delimiter
    - Default: ";"
    - `csv.delimiter` `(string)`

## Structure of saved Json

``` json
{
	"Element Identifier": {
		"value": "VALUE OF Element",
		"label": "Element Label",
		"type": "Element Type (text, email, Textarea ...)"
	},
}
```
Example

``` json
{
	"text-1": {
		"value": "Majd Othman",
		"label": "Name",
		"type": "Text"
	},
	"senderEmail": "majd.othman@exampleemail.com",
	"email-1": {
		"value": "majd.othman@exampleemail.com",
		"label": "E-Mail",
		"type": "Email"
	},
	"telephone-1": {
		"value": "123 456 789",
		"label": "Phone",
		"type": "Telephone"
	},
	"textarea-1": {
		"value": "Message content",
		"label": "Message",
		"type": "Textarea"
	}
}
```


## Screenshot

#### - Add Finisher (screenshot- 1)

![Alt text](./Documentation/screenshot-1.png?raw=true "screenshot 1")

#### - Extension Configuration (screenshot- 2)

![Alt text](./Documentation/screenshot-2.png?raw=true "screenshot 2")

#### - Emails list Configuration to Select the visible fields in the header. This Configuration for current Be-User (screenshot- 3)

![Alt text](./Documentation/screenshot-3.png?raw=true "screenshot 3")

#### - Emails list in Module "Form Data" (screenshot- 4)

![Alt text](./Documentation/screenshot-4.png?raw=true "screenshot 4")
