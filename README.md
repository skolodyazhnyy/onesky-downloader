# Onesky Downloader 

A tiny console application that allows you to download dictionaries created with [Onesky](http://www.oneskyapp.com).

## Usage

Set [Onesky](http://www.oneskyapp.com) API key, secret and project ID in environment variables:
```
export ONESKY_APIKEY=...
export ONESKY_APISECRET=...
export ONESKY_PROJECT=999
```

Run downloader to get your translation files
```
onesky download
# will create a file  for every sourcefile-locale combination
```

## Available options

- `-l` - locale code to download, can be specified multiple times
- `-s` - source file to download
- `-o` - output filename pattern, by default: `[filename].[locale].[extension]`

## More use cases

Download a single source file
```
onesky download -s messages.xliff 
# will create messages.[locale].xliff for every locale
```

Download a single file for specific locales
```
onesky download -s messages.xliff -l en -l es
# will create messages.en.xliff and messages.es.xliff
```

Download a file to specific location
```
onesky download -s messages.xliff -l en -o app/Resources/translations/messages.en.xlf
# will create app/Resources/translations/messages.en.xlf with english translations
```

Download multiple locales to specific location
```
onesky download -s messages.xliff -l en -o app/Resources/translations/messages.[locale].xlf
# will create app/Resources/translations/messages.[locale].xlf for every available locale
```
