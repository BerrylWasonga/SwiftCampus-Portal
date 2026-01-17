# My Project
## Features
- Login system
- Database connection
- Admin dashboard
- User dashboard
- Logout system
- Protect welcome.php properly
- use relative path for images
- Added right padding to the password input so text doesn't overlap the button
- use relative path for files inside folders in the main file
- creating a branch -> git branch <branch_name>
- switching to another branch -> git checkout <branch_name>
- creating and switching to the branch -> git checkout -b <branch_name>
- deleting a branch locally -> git branch -d <branch_name>
- deleting a branch remotely -> git push --deleteorigin <branch_name>
- showing branches -> git branch
- git show
- show only the current branch -> git branch --show-current
- git push origin main -> git push <remote-name> <branch-name>
- check if repository exist -> git remote -v
- switching from one branch to another -> git switch <branch-name>
- Please commit your changes or stash them before you switch branches.
- Git integrates all changes from other into main -> git merge other
- This command renames your current branch to main. For example, if your branch was previously called master, this will rename it to main. -> git branch -M main
- This command links your local repository to a remote repository on GitHub. After this, Git knows where to push your changes online. -> git remote add origin https://github.com/YOUR_USERNAME/pms-project.git
- | Command                               | Feature branch starts from |
| ------------------------------------- | -------------------------- |
| `git checkout -b feature`             | **Current branch**         |
| `git checkout -b feature main`        | Local `main`               |
| `git checkout -b feature origin/main` | Remote `main`              |

- how to revert a commit -> git revert <commit hash b02a271>
- Check the result -> git log --oneline
- check php version using the -> Get-ChildItem -Recurse -Filter *.php | Select-String "readonly class"
- git reset --soft HEAD
- git reset --soft HEAD~1
- git reset --soft HEAD~2
- git reset --soft 6de0b4e
- git reset --mixed HEAD^
- git reset --soft 6de0b4e

- lint all PHP files in PowerShell -> Get-ChildItem -Recurse *.php | % { php -l $_ }


git log

this brach irs not in the remote repository