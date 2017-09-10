# PAAS 平台通用 RBAC 管理系统



## 平台结构说明
```
平台 ┬─ 企业一 ┬─ web 站点一
　　 |  　　　 ├- web 站点二
　　 │  　　　 │  ...
　　 │  　　　 └─ web 站点 N
　　 │
　　 ├- 企业二 ┬─ web 站点一
　　 |  　　　 ├- web 站点二
　　 │  　　　 │  ...
　　 │  　　　 └─ web 站点 N
　　 │  ...
　　 │
　　 └- 企业 N ┬─ web 站点一
　　    　　   ├- web 站点二
　　    　　   │  ...
　　    　　   └─ web 站点 N
```

## 数据模型
### ac_privilege
权限表，记录全系统内所有的权限，例如：修改、增加、读取、删除、执行
* id_privilege 主键
* id_father 父权限的 id_privilege 值。如果该值为 0，表示本身是顶级权限
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读

### ac_resource
资源表，记录系统内所有需要被权限隔离的资源，这些资源分三类：系统的，企业的，web 站点的
* id_resource 主键
* type 0:sys, 1:enteprise, 2:websit ...
* relation_id 关联 id，可能是 管理员 id, 企业 id, websit id, 行为 id 等；也可能是 0，表示无关联项
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读

### ac_rule
角色表，记录系统内所有的角色，这些角色分三类：系统的，企业的，web 站点的
* id_rule 主键
* type 0:sys, 1:enteprise, 2:websit ...
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读
* description 附加描述

### ac_rule_resource_privilege
角色、资源、权限 关联表。一个角色可以关联多个资源及资源相对应的权限，或者关联多个与资源无关的权限
* id
* id_rule
* id_resource
* id_privilege
* description 附加描述

### ac_sys_admin_rule
系统管理员的用户与角色关联表
* id
* id_admin
* id_rule

### ac_enterprise_admin_rule
企业管理员的用户与角色关联表
* id
* id_admin
* id_rule

### ac_user_rule_'网站的 symbol_name'
最终用户的用户与角色关联表
* id
* id_user
* id_rule

## 系统登录入口
PAAS 系统，应该有三个登录入口
1. 系统管理员登录入口
* 入参：用户名，口令
2. 企业管理员登录入口
* 入参：企业符号名，用户名，口令
3. 最终用户登录入口
* 入参：用户名，口令

## 系统管理员
系统管理员用于管理整个系统，他们可以按 "系统 RBAC" 进行分权。
系统管理员可以创建或删除企业账户。
系统管理员存储在表：sys_admin。

## 企业管理员
企业管理员用于管理本企业的各个 web 站点，他们可以按 "企业 RBAC" 进行分权。
企业管理员可以创建或删除本企业的 web 站点，管理本企业的各个 web 站点的最终用户。
系统管理员存储在表：enterprise_admin。

## 最终用户归属
用户只能属于某个 web 站点，所以，每个 web 站点需要有一套：用户表、RBAC。


# 附录：常用制表符
```
┌┬┐  ┌─┐  ┏┳┓  ┏━┓  ┎┰┒  ┍┯┑
├┼┤  │┼│  ┣╋┫  ┃╋┃  ┠╂┨  ┝┿┥
└┴┘  └─┘  ┗┻┛  ┗━┛  ┖┸┚  ┕┷┙

╔╦╗  ╔═╗  ╓╥╖  ╒╤╕
╠╬╣  ║╬║  ╟╫╢  ╞╪╡
╚╩╝  ╚═╝  ╙╨╜  ╘╧╛

┡  ┢  ┱  ┲  ┩ ┧ ┹  ┺  

╃  ╄  ╆  ╅

┽  ╀  ┾  ╁

╊  ╈  ╉  ╇

╭╮  ╲╱
╰╯  ╱╲

╭─╮
│╳│
╰─╯
```