# Bring postnr Fieldtype

ExpressonEngine FieldType that integrates with Norway Post / [Bring API](http://www.bring.no/bring-mail/produkter-og-tjenester/api-for-a-soke-i-postnummer) for validating and storing information about Norwegian postal codes (postnummer).

## Install

Copy the *bring_postnr* folder into your third_party folder and install under Addons -> Fieldtypes.

This will provide you with a text input fieldtype that will validate any input against Bring's API for Norwegian postal codes. In addition information about the postal code will be stored in the database. An error will be thrown if an invalid postal code is entered.

## Tags

Using the fieldname as a tag will produce the postal code (ie. 5306).

Using the field as a tag pair you have some additional information available:

* post_code: The postal code (ie. "5306")
* category: the postal code category ([more info here](http://www.bring.no/hele-bring/forside/_attachment/107479) - [backup](http://pastebin.com/raw.php?i=b4hSeJCX))
* city: uppercase city name, ie. "ERDAL"
* city_lower: lowercase city name, ie "erdal"
* city_ucfirst: First letter uppercase, the rest lower case (ie. "Erdal")

## Parameters

* tag_prefix: specifies the tag prefix for the internal tags. Ie {user_postnr_field tag_prefix="postnr:"} {postnr:city_ucfirst} {/user_postnr_field}

## Channel Forms

The fieldtype can be used with Channel Forms to accept user input. If you have an entry for each user for instance this can be used to store their location, or alternatively grabbing geolocation based on postal code (using Google's API for this, not provided with this addon).

