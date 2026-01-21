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
- reversing a staging message ->git restore --staged .
- git reset -> Oops, I staged too many files. Let me unstage all.

- check php version using the -> Get-ChildItem -Recurse -Filter *.php | Select-String "readonly class"
- git reset --soft HEAD
- git reset --soft HEAD~1
- git reset --soft HEAD~2
- git reset --soft 6de0b4e
- git reset --mixed HEAD^
- git reset --soft 6de0b4e

- lint all PHP files in PowerShell -> Get-ChildItem -Recurse *.php | % { php -l $_ }

- unstage all changes:-> git restore --staged <filename/.>
git log

this brach irs not in the remote repository
git add -u <filename> -> it statges tracked files only
- "new commands" | ForEach-Object { $_ | Tee-Object file4.txt; $_ | Tee-Object file5.txt }

- Use a loop with an array of filenames
   # Define your files
$files = @(
    "file1.txt","file2.txt","file3.txt","file4.txt",
    "file5.txt","file6.txt","file7.txt","file8.txt",
    "file9.txt","file10.txt","file11.txt","file12.txt",
    "file13.txt","file14.txt","file15.txt","file16.txt"
)

# Define your content (can be multi-line code)
$content = @"
echo "Line 1"
echo "Line 2"
# Add more code here
"@

# Write content to all files
foreach ($f in $files) {
    $content | Set-Content $f
}

- how to create a file -> New-Item <filename>
- modyfiying existing -> echo "new line" >> file1.txt
- if you use echo "new line" > file1.txt on exiting file it overides everything
- remove a file rm <filename>

- This is the big difference:

git add . → relative to current folder

git add -A → absolute repo-wide, stages everything