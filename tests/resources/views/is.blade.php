@is($role1)
user has {{ $role1->slug }}
@elseis($role2)
user has {{ $role2->slug }}
@else
user does not have role
@endis
