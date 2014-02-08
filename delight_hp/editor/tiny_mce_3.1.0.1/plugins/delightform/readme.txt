delight CMS Forms-Plugin for tinyMCE

we insert a new userdefined tag named "dedtform" which has attributes to be parsed while save the content.

Attributes:
  name      : User can define the name for the Form. default is "form[TEXTID]"
  encoding  : User can override the encoding here. default is "application/x-www-form-urlencoded"
  method    : Method to process formular-data (currently only "mail" is available)
  validate  : if set to true, the fields with class "mandatory" will be checked against the other class "mail" or "number"
  onsuccess : URI to redirect after successfull processing submitted formular
  onfailure : URI to redirect after failure while processing submitted formular
  
Mail-Method-Specific Attributes:
  mail_rcpt        : Recipient Emailaddress
  mail_rcptname    : Recipient name (Anme, Surname, company, etc.)
  mail_subject     : Subject for the Mail (recipient and sender - sender only if mail_inform is true)
  mail_inform      : if true, the sender wil also be notified about the email
  mail_senderfield : name of the field which holds the sender-emailaddress
  mail_pretext     : Initial Text to append to the Mailbody (can have [NameOfFormularField] tags to use values from the Formular)
  mail_posttext    : Text to appnd after all Formular-Fields to the Mailbody (can have [NameOfFormularField] tags to use values from the Formular)
