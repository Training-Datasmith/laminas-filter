<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function array_replace_recursive;
use function get_debug_type;
use function is_callable;
use Laminas\Service_Manager\Abstract_Plugin_Manager;
use Laminas\Service_Manager\Exception\Invalid_Service_Exception;
use Laminas\Service_Manager\Factory\Invokable_Factory;
use Laminas\Service_Manager\Service_Manager;
use Psr\Container\Container_Interface;
use function sprintf;
/**
 * Plugin manager implementation for filters
 *
 * Enforces that filters retrieved are either callbacks or instances of FilterInterface.
 *
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 * @extends AbstractPluginManager<InstanceType>
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Filter_Plugin_Manager extends Abstract_Plugin_Manager
{
    private const CONFIGURATION = ['factories' => [Allow_List::class => Invokable_Factory::class, Base_Name::class => Invokable_Factory::class, Boolean::class => Invokable_Factory::class, Callback::class => Invokable_Factory::class, Compress_String::class => Invokable_Factory::class, Compress_To_Archive::class => Invokable_Factory::class, Data_Unit_Formatter::class => Invokable_Factory::class, Date_Select::class => Invokable_Factory::class, Date_Time_Formatter::class => Invokable_Factory::class, Date_Time_Select::class => Invokable_Factory::class, Decompress_Archive::class => Invokable_Factory::class, Decompress_String::class => Invokable_Factory::class, Deny_List::class => Invokable_Factory::class, Digits::class => Invokable_Factory::class, Dir::class => Invokable_Factory::class, File\Lower_Case::class => Invokable_Factory::class, File\Rename::class => Invokable_Factory::class, File\Rename_Upload::class => Invokable_Factory::class, File\Upper_Case::class => Invokable_Factory::class, Filter_Chain::class => Filter_Chain_Factory::class, Force_Uri_Scheme::class => Invokable_Factory::class, Html_Entities::class => Invokable_Factory::class, Immutable_Filter_Chain::class => Immutable_Filter_Chain_Factory::class, Inflector::class => Inflector_Factory::class, To_Float::class => Invokable_Factory::class, Month_Select::class => Invokable_Factory::class, Upper_Case_Words::class => Invokable_Factory::class, Preg_Replace::class => Invokable_Factory::class, Real_Path::class => Invokable_Factory::class, String_Prefix::class => Invokable_Factory::class, String_Suffix::class => Invokable_Factory::class, String_To_Lower::class => Invokable_Factory::class, String_To_Upper::class => Invokable_Factory::class, String_Trim::class => Invokable_Factory::class, Strip_Newlines::class => Invokable_Factory::class, Strip_Tags::class => Invokable_Factory::class, To_Enum::class => Invokable_Factory::class, To_Int::class => Invokable_Factory::class, To_Null::class => Invokable_Factory::class, To_String::class => Invokable_Factory::class, Word\Camel_Case_To_Dash::class => Invokable_Factory::class, Word\Camel_Case_To_Separator::class => Invokable_Factory::class, Word\Camel_Case_To_Underscore::class => Invokable_Factory::class, Word\Dash_To_Camel_Case::class => Invokable_Factory::class, Word\Dash_To_Separator::class => Invokable_Factory::class, Word\Dash_To_Underscore::class => Invokable_Factory::class, Word\Separator_To_Camel_Case::class => Invokable_Factory::class, Word\Separator_To_Dash::class => Invokable_Factory::class, Word\Separator_To_Separator::class => Invokable_Factory::class, Word\Underscore_To_Camel_Case::class => Invokable_Factory::class, Word\Underscore_To_Studly_Case::class => Invokable_Factory::class, Word\Underscore_To_Dash::class => Invokable_Factory::class, Word\Underscore_To_Separator::class => Invokable_Factory::class], 'aliases' => [
        // For the future
        'int' => To_Int::class,
        'Int' => To_Int::class,
        'null' => To_Null::class,
        'Null' => To_Null::class,
        // Standard filters
        'allowlist' => Allow_List::class,
        'allowList' => Allow_List::class,
        'AllowList' => Allow_List::class,
        'basename' => Base_Name::class,
        'Basename' => Base_Name::class,
        'boolean' => Boolean::class,
        'Boolean' => Boolean::class,
        'callback' => Callback::class,
        'Callback' => Callback::class,
        'dataunitformatter' => Data_Unit_Formatter::class,
        'dataUnitFormatter' => Data_Unit_Formatter::class,
        'DataUnitFormatter' => Data_Unit_Formatter::class,
        'dateselect' => Date_Select::class,
        'dateSelect' => Date_Select::class,
        'DateSelect' => Date_Select::class,
        'datetimeformatter' => Date_Time_Formatter::class,
        'datetimeFormatter' => Date_Time_Formatter::class,
        'DatetimeFormatter' => Date_Time_Formatter::class,
        'dateTimeFormatter' => Date_Time_Formatter::class,
        'DateTimeFormatter' => Date_Time_Formatter::class,
        'datetimeselect' => Date_Time_Select::class,
        'datetimeSelect' => Date_Time_Select::class,
        'DatetimeSelect' => Date_Time_Select::class,
        'dateTimeSelect' => Date_Time_Select::class,
        'DateTimeSelect' => Date_Time_Select::class,
        'denylist' => Deny_List::class,
        'denyList' => Deny_List::class,
        'DenyList' => Deny_List::class,
        'digits' => Digits::class,
        'Digits' => Digits::class,
        'dir' => Dir::class,
        'Dir' => Dir::class,
        'filelowercase' => File\Lower_Case::class,
        'fileLowercase' => File\Lower_Case::class,
        'FileLowercase' => File\Lower_Case::class,
        'fileLowerCase' => File\Lower_Case::class,
        'FileLowerCase' => File\Lower_Case::class,
        'filerename' => File\Rename::class,
        'fileRename' => File\Rename::class,
        'FileRename' => File\Rename::class,
        'filerenameupload' => File\Rename_Upload::class,
        'fileRenameUpload' => File\Rename_Upload::class,
        'FileRenameUpload' => File\Rename_Upload::class,
        'fileuppercase' => File\Upper_Case::class,
        'fileUppercase' => File\Upper_Case::class,
        'FileUppercase' => File\Upper_Case::class,
        'fileUpperCase' => File\Upper_Case::class,
        'FileUpperCase' => File\Upper_Case::class,
        'htmlentities' => Html_Entities::class,
        'htmlEntities' => Html_Entities::class,
        'HtmlEntities' => Html_Entities::class,
        'inflector' => Inflector::class,
        'Inflector' => Inflector::class,
        'monthselect' => Month_Select::class,
        'monthSelect' => Month_Select::class,
        'MonthSelect' => Month_Select::class,
        'pregreplace' => Preg_Replace::class,
        'pregReplace' => Preg_Replace::class,
        'PregReplace' => Preg_Replace::class,
        'realpath' => Real_Path::class,
        'realPath' => Real_Path::class,
        'RealPath' => Real_Path::class,
        'stringprefix' => String_Prefix::class,
        'stringPrefix' => String_Prefix::class,
        'StringPrefix' => String_Prefix::class,
        'stringsuffix' => String_Suffix::class,
        'stringSuffix' => String_Suffix::class,
        'StringSuffix' => String_Suffix::class,
        'stringtolower' => String_To_Lower::class,
        'stringToLower' => String_To_Lower::class,
        'StringToLower' => String_To_Lower::class,
        'stringtoupper' => String_To_Upper::class,
        'stringToUpper' => String_To_Upper::class,
        'StringToUpper' => String_To_Upper::class,
        'stringtrim' => String_Trim::class,
        'stringTrim' => String_Trim::class,
        'StringTrim' => String_Trim::class,
        'stripnewlines' => Strip_Newlines::class,
        'stripNewlines' => Strip_Newlines::class,
        'StripNewlines' => Strip_Newlines::class,
        'striptags' => Strip_Tags::class,
        'stripTags' => Strip_Tags::class,
        'StripTags' => Strip_Tags::class,
        'toint' => To_Int::class,
        'toInt' => To_Int::class,
        'ToInt' => To_Int::class,
        'tofloat' => To_Float::class,
        'toFloat' => To_Float::class,
        'ToFloat' => To_Float::class,
        'tonull' => To_Null::class,
        'toNull' => To_Null::class,
        'ToNull' => To_Null::class,
        'uppercasewords' => Upper_Case_Words::class,
        'upperCaseWords' => Upper_Case_Words::class,
        'UpperCaseWords' => Upper_Case_Words::class,
        'wordcamelcasetodash' => Word\Camel_Case_To_Dash::class,
        'wordCamelCaseToDash' => Word\Camel_Case_To_Dash::class,
        'WordCamelCaseToDash' => Word\Camel_Case_To_Dash::class,
        'wordcamelcasetoseparator' => Word\Camel_Case_To_Separator::class,
        'wordCamelCaseToSeparator' => Word\Camel_Case_To_Separator::class,
        'WordCamelCaseToSeparator' => Word\Camel_Case_To_Separator::class,
        'wordcamelcasetounderscore' => Word\Camel_Case_To_Underscore::class,
        'wordCamelCaseToUnderscore' => Word\Camel_Case_To_Underscore::class,
        'WordCamelCaseToUnderscore' => Word\Camel_Case_To_Underscore::class,
        'worddashtocamelcase' => Word\Dash_To_Camel_Case::class,
        'wordDashToCamelCase' => Word\Dash_To_Camel_Case::class,
        'WordDashToCamelCase' => Word\Dash_To_Camel_Case::class,
        'worddashtoseparator' => Word\Dash_To_Separator::class,
        'wordDashToSeparator' => Word\Dash_To_Separator::class,
        'WordDashToSeparator' => Word\Dash_To_Separator::class,
        'worddashtounderscore' => Word\Dash_To_Underscore::class,
        'wordDashToUnderscore' => Word\Dash_To_Underscore::class,
        'WordDashToUnderscore' => Word\Dash_To_Underscore::class,
        'wordseparatortocamelcase' => Word\Separator_To_Camel_Case::class,
        'wordSeparatorToCamelCase' => Word\Separator_To_Camel_Case::class,
        'WordSeparatorToCamelCase' => Word\Separator_To_Camel_Case::class,
        'wordseparatortodash' => Word\Separator_To_Dash::class,
        'wordSeparatorToDash' => Word\Separator_To_Dash::class,
        'WordSeparatorToDash' => Word\Separator_To_Dash::class,
        'wordseparatortoseparator' => Word\Separator_To_Separator::class,
        'wordSeparatorToSeparator' => Word\Separator_To_Separator::class,
        'WordSeparatorToSeparator' => Word\Separator_To_Separator::class,
        'wordunderscoretocamelcase' => Word\Underscore_To_Camel_Case::class,
        'wordUnderscoreToCamelCase' => Word\Underscore_To_Camel_Case::class,
        'WordUnderscoreToCamelCase' => Word\Underscore_To_Camel_Case::class,
        'wordunderscoretostudlycase' => Word\Underscore_To_Studly_Case::class,
        'wordUnderscoreToStudlyCase' => Word\Underscore_To_Studly_Case::class,
        'WordUnderscoreToStudlyCase' => Word\Underscore_To_Studly_Case::class,
        'wordunderscoretodash' => Word\Underscore_To_Dash::class,
        'wordUnderscoreToDash' => Word\Underscore_To_Dash::class,
        'WordUnderscoreToDash' => Word\Underscore_To_Dash::class,
        'wordunderscoretoseparator' => Word\Underscore_To_Separator::class,
        'wordUnderscoreToSeparator' => Word\Underscore_To_Separator::class,
        'WordUnderscoreToSeparator' => Word\Underscore_To_Separator::class,
    ]];
    /** Filter instances are never shared */
    protected bool $shared_by_default = false;
    /** Generally speaking, filters can be constructed without arguments */
    protected bool $auto_add_invokable_class = true;
    /**
     * @param ServiceManagerConfiguration $config
     */
    public function __construct(Container_Interface $creation_context, array $config = [])
    {
        /** @var ServiceManagerConfiguration $config */
        $config = array_replace_recursive(self::CONFIGURATION, $config);
        parent::__construct($creation_context, $config);
    }
    /** @inheritDoc */
    public function validate(mixed $instance): void
    {
        if ($instance instanceof Filter_Interface) {
            return;
        }
        if (is_callable($instance)) {
            return;
        }
        throw new Invalid_Service_Exception(sprintf('Plugin of type %s is invalid; must implement %s\FilterInterface or be callable', get_debug_type($instance), __NAMESPACE__));
    }
}