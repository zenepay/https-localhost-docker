#! /bin/bash
search_dir='./app/Models/'
for entry in $(find $search_dir -print)
do
if [ -f "$entry" ];then
class_name=$(echo "$entry" | cut -f2 -d'.' | cut -f4- -d'/' | sed 's/\//\\/g')
if [ "$class_name" != 'User' ];
then
echo "$class_name"
#php artisan make:filament-resource "$class_name" --generate --view
fi
fi
done