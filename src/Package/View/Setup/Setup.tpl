{{R3M}}
{{$register = Package.R3m.Io.Email:Init:register()}}
{{if(!is.empty($register))}}
{{Package.R3m.Io.Email:Import:role.system()}}
{{Package.R3m.Io.Email:Import:config.email()}}
{{$options = options()}}
{{/if}}