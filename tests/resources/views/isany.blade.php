@isany(['role-3', $role2])
user has role-3 or {{ $role2->slug }}
@elseisany(['role-3', $role1])
user has role-3 or {{ $role1->slug }}
@else
user does not have role
@endisany
