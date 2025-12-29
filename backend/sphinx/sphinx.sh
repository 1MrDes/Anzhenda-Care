#!/bin/sh
if [ -f ~/.bash_profile ];
then
  . ~/.bash_profile
fi

########################-------生成主索引 START-------#########################

main_fun()
{
  # 征婚人
  /usr/local/sphinxforchinese/bin/indexer -c /data/sphinx/sphinx.conf marriage_seeking_member_profile --rotate
}

########################-------生成主索引 END-------#########################

#########################-------建立增量索引 START-------#########################

delta_fun()
{
  # 征婚人
  /usr/local/sphinxforchinese/bin/indexer -c /data/sphinx/sphinx.conf delta_marriage_seeking_member_profile --rotate
}

#########################-------建立增量索引 END -------#########################


########################-------合并索引文件 START-------#########################

merge_fun()
{
  # 征婚人
  /usr/local/sphinxforchinese/bin/indexer -c /data/sphinx/sphinx.conf --merge marriage_seeking_member_profile delta_marriage_seeking_member_profile --rotate
}

########################-------合并索引文件 END-------#########################


#置前索引合并（刚执行完全量索引时要先执行一次，以防覆盖运行全量索引时生成的增量索引）
if [ $1 = "--all" ]; then
  res=`main_fun`
  echo "生成主索引： "${res}
  delta_fun
fi

#生成增量索引
if [ $1 = "--delta" ]; then
  #执行更新增量索引
  res_delta=`delta_fun`
  echo "生成增量索引： "${res_delta}
fi

echo "succesfully"

