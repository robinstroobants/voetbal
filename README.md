# voetbal
Branching — vuistregel
Kleine fix: werk direct op beta, dan deploy-uat.sh
Nieuwe feature: git checkout -b feature/naam (op basis van beta), dan merge naar beta als klaar
Nooit op main werken — enkel git merge beta na UAT approval
# Na prod deploy, éénmalig taggen:
git checkout main && git merge beta
./scripts/deploy-prod.sh
git tag v3.0.0
git push origin v3.0.0

