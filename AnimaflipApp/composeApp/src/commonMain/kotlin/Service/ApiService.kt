package Service

import Model.Theme
import androidx.compose.runtime.Composable
//import org.jetbrains.compose.resources.painterResource
import animaflipapp.composeapp.generated.resources.Res

// Assurez-vous d'importer votre package correctement

class ApiService {
    fun login(email: String, password: String) : Boolean {
        if(email == "admin@admin.com" && password == "admin")
        {
            return true;
        } else {
            return false;
        }
    }

    @Composable
    fun getSampleThemes(): List<Theme> {
        return listOf(
            Theme(
                id = 1,
                name = "Animaux domestiques",
                animations = listOf(
//                    painterResource(Res.drawable.fr),
//                    painterResource(Res.drawable.eg),
//                    painterResource(Res.drawable.id)
                )
            ),
            Theme(
                id = 2,
                name = "Météo",
                animations = listOf(
//                    painterResource(Res.drawable.fr),
//                    painterResource(Res.drawable.eg),
//                    painterResource(Res.drawable.id)
                )
            ),
            Theme(
                id = 3,
                name = "Moyens de transport",
                animations = listOf(
//                    painterResource(Res.drawable.fr),
//                    painterResource(Res.drawable.eg),
//                    painterResource(Res.drawable.id)
                )
            ),
            Theme(
                id = 4,
                name = "Fruits et légumes",
                animations = listOf(
//                    painterResource(Res.drawable.fr),
//                    painterResource(Res.drawable.eg),
//                    painterResource(Res.drawable.id)
                )
            ),
            Theme(
                id = 5,
                name = "Couleurs",
                animations = listOf(
//                    painterResource(Res.drawable.fr),
//                    painterResource(Res.drawable.eg),
//                    painterResource(Res.drawable.id)
                )
            )
        )
    }
}