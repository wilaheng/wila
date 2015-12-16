<?php
return array(
    "root" => true,
    "domain_name" => "wila.com",
    "maxlist_module" => 8,
    "default_theme" => "default",
    "session_stack" => 1000,
    "session" => "vfs/SSO",
    "execute" => "HEAD",
    "object" => "object",
    "singleton" => "singleton",
    "service" => "service",
    "secure_service" => "secure-service",
    "session_service" => "session",
    "no_context" => "no-context",
    "xreq" => "HTTP_X_REQUESTED_WITH",
    "ajax" => "XMLHttpRequest",
    "singletonException" => "SingletonException: %s",
    "ajaxException" => "The requested method %s.%s expected ajax header",
    "argumentException" => "ArgumentLengthException: expected %d, received %d",
    "regexException" => "RegexException: expected argument %s as regexp (%s), received %s",
    "methodException" => "The requested method %s.%s does not exist",
    "moduleNotFound" => "The requested module %s was not found on this server.",
    "requiredException" => "RequiredParameterException: %s",
    "resourceNameException" => "ResourceNameException: find %s, found %s",
    "resourceNotFound" => "ResourceNotFoundException: %s",
    "accessException" => "AccessException: %s.%s",
    "alnum" => "expected argument %s as alphanumeric, received %s",
    "alpha" => "expected argument %s as alphabetic, received %s",
    "digit" => "expected argument %s as digit, received %s",
    "email" => "expected argument %s as email, received %s",
    "bool" => "expected argument %s as boolean, received %s",
    "date" => "expected %s as date, received %s",
    "length" => "expected argument (%s), %s (%d)",
    "value" => "expected argument (%s) %s %s %s",
    "expect" => "Expect should be an array"
);
?>