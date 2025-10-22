Push:  to Github crm3 as subtree
git subtree push --prefix=crm3 crm3-public main

If you get "src refspec main does not match any", the public repo might expect master:

Pull
git subtree push --prefix=crm3 crm3-public master



