如果你在使用 git push 提交代码到 GitHub 时遇到：

rust
复制
编辑
fatal: Authentication failed for 'https://github.com/yourname/yourrepo.git/'
这是因为 GitHub 不再支持使用用户名+密码的方式进行 HTTPS 鉴权，而是需要你使用：

✅ 方法一：Personal Access Token（替代密码）
打开：
👉 https://github.com/settings/tokens

点击 “Generate new token (classic)”

填写名称

勾选 repo 权限（如果你要操作私有仓库）

设置有效期（比如 90 天）

生成后复制 Token（只显示一次）

回到终端执行 git push 时，当它要求你输入“password”时：

用户名仍输入你的 GitHub 用户名

密码处粘贴刚刚生成的 Token

✅ 之后 Git 会记住这个 Token，不用每次都输入（取决于你电脑的凭据存储设置）。

✅ 方法二：改用 SSH（推荐长期使用）
生成 SSH 密钥：

bash
复制
编辑
ssh-keygen -t ed25519 -C "your_email@example.com"


ssh-keygen -t ed25519 -C "honey@163.com"
一路回车，默认路径即可。

示例：生成多个 SSH Key
bash
复制
编辑
ssh-keygen -t ed25519 -C "gitea@yourdomain.com" -f ~/.ssh/id_ed25519_gitea
ssh-keygen -t ed25519 -C "github@yourdomain.com" -f ~/.ssh/id_ed25519_github

添加 SSH 密钥到 GitHub：

bash
复制
编辑
pbcopy < ~/.ssh/id_ed25519.pub
然后打开 GitHub：
👉 https://github.com/settings/keys
点击 New SSH Key，粘贴进去。
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAICRlfdeqT4iTwYky8i2P53R0S4V1IY0eV+U58ghcrEw/ honey@163.com

修改远程地址为 SSH：

SHA256:EUlCtzJPb28Pjc5ynMmLM7t2Uo8G1yu+/3iJk5PPNkg honey@163.com

bash
复制
编辑
git remote set-url origin git@github.com:yourname/yourrepo.git
再次提交：

bash
复制
编辑
git push origin main
🧪 检查当前仓库使用的是 HTTPS 还是 SSH：
bash
复制
编辑
git remote -v
如果看到的是类似：

arduino
复制
编辑
https://github.com/xxx/xxx.git
说明你正在用 HTTPS，推荐改为 SSH。

如果你告诉我你的项目仓库地址（比如是不是你自己的，是否私有），我可以帮你写出完整命令或修正你的 Git 配置。要不要我带你一步步改为 SSH？

URL=https://www.aapanel.com/script/install_7.0_en.sh && if [ -f /usr/bin/curl ];then curl -ksSO "$URL" ;else wget --no-check-certificate -O install_7.0_en.sh "$URL";fi;bash install_7.0_en.sh aapanel

git remote set-url origin git@github.com:windowen/pyun2.git



location / {
###The following is the pseudo-static rewrite rule for PHPCMS
rewrite ^(.*)show-([0-9]+)-([0-9]+)\.html$ $1/show.php?itemid=$2&page=$3;
rewrite ^(.*)list-([0-9]+)-([0-9]+)\.html$ $1/list.php?catid=$2&page=$3;
rewrite ^(.*)show-([0-9]+)\.html$ $1/show.php?specialid=$2;
####Here are the PHPWind pseudo-static rewrite rules
rewrite ^(.*)-htm-(.*)$ $1.php?$2 last;
rewrite ^(.*)/simple/([a-z0-9\_]+\.html)$ $1/simple/index.php?$2 last;
}