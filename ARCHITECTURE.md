# Architecture: laminas-filter

## Purpose
A composable PHP filtering/sanitization library. Provides 50+ individual filter implementations (string manipulation, type coercion, file operations, compression) that can be chained together via `Filter_Chain`.

## Directory Structure
```
src/
  Filter_Interface.php          # filter($value): mixed — the single contract all filters implement
  Filter_Chain.php              # Applies a sequence of filters in order; supports priorities
  Immutable_Filter_Chain.php    # Immutable variant — returns new chain on modification
  Filter_Plugin_Manager.php     # ServiceManager plugin manager for filter discovery/instantiation
  # String filters:
  String_To_Lower.php / String_To_Upper.php / String_Trim.php / Strip_Tags.php
  Strip_Newlines.php / Digits.php / Preg_Replace.php / Html_Entities.php
  String_Prefix.php / String_Suffix.php / Upper_Case_Words.php / Base_Name.php
  # Type coercion:
  Boolean.php / To_Int.php / To_Float.php / To_Null.php / To_String.php / To_Enum.php
  # Word-case transformers:
  Word/Camel_Case_To_Dash.php / Camel_Case_To_Underscore.php / Underscore_To_Camel_Case.php
  Word/Dash_To_Camel_Case.php / Separator_To_Camel_Case.php / etc.
  # List filters:
  Allow_List.php / Deny_List.php
  # File filters:
  File/Rename.php / Rename_Upload.php / Move_Uploaded_File.php / Filter_File_Contents.php
  # Compression:
  Compress_String.php / Decompress_String.php
  Compress_To_Archive.php / Decompress_Archive.php
  Compress/Bz2Adapter.php / Gz_Adapter.php / Tar_Adapter.php / Zip_Adapter.php
  # Misc:
  Callback.php / Date_Time_Formatter.php / Data_Unit_Formatter.php
  Inflector.php                # Word-inflection filter (delegates to laminas/laminas-filter internals)
  Force_Uri_Scheme.php
  Exception/                   # Typed exceptions
  Module.php / Config_Provider.php
```

## Key Design Decisions
- **Single interface** — every filter implements `filter($value): mixed`, making all filters composable regardless of input/output type.
- **Filter chain priorities** — `Filter_Chain` stores filters in a priority queue; higher priority = runs first. Useful when one filter's output feeds into another's input.
- **Immutable variant** — `Immutable_Filter_Chain` allows filter chains to be shared safely across requests without mutation concerns.
- **Plugin manager** — `Filter_Plugin_Manager` enables short-name aliases (e.g., `'StringToLower'` instead of FQCN) and lazy instantiation with option injection.
- **Compression adapter strategy** — archive compression delegates to swappable adapters (`Bz2`, `Gz`, `Tar`, `Zip`), selected by MIME type or file extension.

## Extension Points
- Implement `Filter_Interface` to create a custom filter.
- Register a custom filter alias in `Filter_Plugin_Manager` via configuration.
- Compose multiple filters using `Filter_Chain` or `Immutable_Filter_Chain`.

## Dependency Flow
```
FilterChain::filter($value)
  └─ [priority queue iteration]
       ├─ Filter1::filter($value) → $value1
       ├─ Filter2::filter($value1) → $value2
       └─ FilterN::filter($valueN-1) → final output
```
